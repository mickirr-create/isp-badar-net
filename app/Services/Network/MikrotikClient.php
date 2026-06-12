<?php

namespace App\Services\Network;

use RuntimeException;

class MikrotikClient
{
    private $socket = null;
    private bool $connected = false;

    public function __construct(
        private readonly string $ip,
        private readonly int $port = 8728,
        private readonly string $username = 'admin',
        private readonly string $password = '',
    ) {}

    public function connect(): void
    {
        $host = $this->ip;
        $port = $this->port;

        $this->socket = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$this->socket) {
            throw new RuntimeException("Failed to connect to MikroTik at {$host}:{$port} - {$errstr} ({$errno})");
        }

        socket_set_timeout($this->socket, 5, 0);
        $this->login();
        $this->connected = true;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
        $this->connected = false;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    private function login(): void
    {
        $response = $this->sendCommand('/login');
        $challenge = '';

        foreach ($response as $sentence) {
            if (isset($sentence['=ret'])) {
                $challenge = $sentence['=ret'];
            }
        }

        if (!empty($challenge)) {
            $challenge = pack('H*', md5("\0" . $this->password . hex2bin($challenge)));
            $response = $this->sendCommand('/login', ['username' => $this->username, 'response' => $challenge]);
        } else {
            $response = $this->sendCommand('/login', ['username' => $this->username, 'password' => $this->password]);
        }

        foreach ($response as $sentence) {
            if (isset($sentence['!trap']) || isset($sentence['=error'])) {
                throw new RuntimeException("MikroTik login failed: " . ($sentence['=message'] ?? 'Unknown error'));
            }
        }
    }

    public function sendCommand(string $command, array $args = []): array
    {
        if (!$this->socket) {
            throw new RuntimeException("Not connected to MikroTik");
        }

        $words = [$command];
        foreach ($args as $key => $value) {
            $words[] = '=' . $key . '=' . $value;
        }

        $this->writeSentence($words);
        return $this->readResponse();
    }

    public function sendRequest(string $path, array $args = [], ?string $query = null): array
    {
        if (!$this->socket) {
            throw new RuntimeException("Not connected to MikroTik");
        }

        $words = [$path];
        foreach ($args as $key => $value) {
            if (is_int($key)) {
                $words[] = '=' . $value;
            } else {
                $words[] = '=' . $key . '=' . $value;
            }
        }

        if ($query) {
            $words[] = '?=' . $query;
        }

        $this->writeSentence($words);
        return $this->readResponse();
    }

    public function findAndRemove(string $path, string $field, string $value): bool
    {
        $printPath = $path . ' print .proplist=.id';
        $response = $this->sendRequest($printPath, [], "{$field}={$value}");

        foreach ($response as $sentence) {
            if (isset($sentence['=.id'])) {
                $this->sendCommand($path . '/remove', ['numbers' => $sentence['=.id']]);
                return true;
            }
        }
        return false;
    }

    public function findAndSet(string $path, string $field, string $value, array $setArgs): bool
    {
        $printPath = $path . ' print .proplist=.id';
        $response = $this->sendRequest($printPath, [], "{$field}={$value}");

        foreach ($response as $sentence) {
            if (isset($sentence['=.id'])) {
                $setArgs['numbers'] = $sentence['=.id'];
                $this->sendCommand($path . '/set', $setArgs);
                return true;
            }
        }
        return false;
    }

    public function findOrCreate(string $path, string $field, string $value, array $addArgs): bool
    {
        $printPath = $path . ' print .proplist=.id';
        $response = $this->sendRequest($printPath, [], "{$field}={$value}");

        foreach ($response as $sentence) {
            if (isset($sentence['=.id'])) {
                $setArgs = array_merge(['numbers' => $sentence['=.id']], $addArgs);
                $this->sendCommand($path . '/set', $setArgs);
                return true;
            }
        }

        $this->sendCommand($path . '/add', $addArgs);
        return true;
    }

    private function writeSentence(array $words): void
    {
        $data = '';
        foreach ($words as $word) {
            $len = strlen($word);
            $data .= chr(($len >> 24) & 0xFF);
            $data .= chr(($len >> 16) & 0xFF);
            $data .= chr(($len >> 8) & 0xFF);
            $data .= chr($len & 0xFF);
            $data .= $word;
        }

        $written = @fwrite($this->socket, $data);
        if ($written === false) {
            throw new RuntimeException("Failed to write to MikroTik socket");
        }
    }

    private function readResponse(): array
    {
        $sentences = [];
        $currentSentence = [];

        while (true) {
            $header = $this->readBytes(4);
            if ($header === null) {
                break;
            }

            $length = unpack('N', $header)[1];
            if ($length == 0) {
                break;
            }

            $word = $this->readBytes($length);
            if ($word === null) {
                break;
            }

            if ($word[0] === '.') {
                $currentSentence[$word] = true;
            } elseif ($word[0] === '=') {
                $parts = explode('=', substr($word, 1), 2);
                $currentSentence[$parts[0]] = $parts[1] ?? '';
            } elseif ($word[0] === '!') {
                $currentSentence[$word] = true;
                $sentences[] = $currentSentence;
                $currentSentence = [];

                if ($word === '!done' || $word === '!trap') {
                    break;
                }
            } else {
                $currentSentence[$word] = true;
            }
        }

        if (!empty($currentSentence)) {
            $sentences[] = $currentSentence;
        }

        return $sentences;
    }

    private function readBytes(int $length): ?string
    {
        $data = '';
        while (strlen($data) < $length) {
            $chunk = @fread($this->socket, $length - strlen($data));
            if ($chunk === false || $chunk === '') {
                return null;
            }
            $data .= $chunk;
        }
        return $data;
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
