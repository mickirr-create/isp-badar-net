<?php

namespace App\Services\Network\Drivers;

use App\Models\Bandwidth;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Pool;
use App\Models\Router;
use App\Services\Network\MikrotikClient;
use App\Services\Network\NetworkDeviceAdapter;
use Illuminate\Support\Facades\Log;

class MikrotikPppoeDriver implements NetworkDeviceAdapter
{
    private MikrotikClient $client;

    public function __construct(private readonly Router $router)
    {
        $this->client = new MikrotikClient(
            $router->ip_address,
            8728,
            $router->username,
            $router->password
        );
    }

    public function addCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $this->client->connect();

            $isExp = Plan::where('plan_expired', $plan->id)->first();
            $existingId = $this->findPpoeSecretId($customer);

            if ($existingId) {
                $args = [
                    'numbers' => $existingId,
                    'password' => !empty($customer->pppoe_password) ? $customer->pppoe_password : $customer->password,
                    'name' => !empty($customer->pppoe_username) ? $customer->pppoe_username : $customer->username,
                    'profile' => $plan->name_plan,
                    'comment' => $customer->fullname . ' | ' . ($customer->email ?? ''),
                ];

                if (!empty($customer->pppoe_ip) && !$isExp) {
                    $args['remote-address'] = $customer->pppoe_ip;
                }

                $this->client->sendCommand('/ppp/secret/set', $args);

                if (empty($customer->pppoe_ip) || $isExp) {
                    try {
                        $this->client->sendCommand('/ppp/secret/unset', [
                            '.id' => $existingId,
                            'value-name' => 'remote-address',
                        ]);
                    } catch (\Throwable $e) {
                        // Ignore unset errors
                    }
                }
            } else {
                $addArgs = [
                    'service' => 'pppoe',
                    'profile' => $plan->name_plan,
                    'comment' => $customer->fullname . ' | ' . ($customer->email ?? ''),
                    'password' => !empty($customer->pppoe_password) ? $customer->pppoe_password : $customer->password,
                    'name' => !empty($customer->pppoe_username) ? $customer->pppoe_username : $customer->username,
                ];

                if (!empty($customer->pppoe_ip) && !$isExp) {
                    $addArgs['remote-address'] = $customer->pppoe_ip;
                }

                $this->client->sendCommand('/ppp/secret/add', $addArgs);
            }

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe addCustomer failed: {$e->getMessage()}", [
                'customer' => $customer->username,
                'plan' => $plan->name_plan,
            ]);
            return false;
        }
    }

    public function removeCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $this->client->connect();

            if (!empty($plan->plan_expired)) {
                $expiredPlan = Plan::find($plan->plan_expired);
                if ($expiredPlan) {
                    $this->addCustomer($customer, $expiredPlan);
                    $this->removeActivePpoe($customer);
                    $this->client->disconnect();
                    return true;
                }
            }

            $this->client->findAndRemove('/ppp/secret', 'name', $customer->username);
            if (!empty($customer->pppoe_username)) {
                $this->client->findAndRemove('/ppp/secret', 'name', $customer->pppoe_username);
            }

            $this->removeActivePpoe($customer);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe removeCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function syncCustomer(Customer $customer, Plan $plan): bool
    {
        return $this->addCustomer($customer, $plan);
    }

    public function addPlan(Plan $plan): bool
    {
        try {
            $this->client->connect();

            $bw = Bandwidth::find($plan->id_bw);
            $rate = $this->computeRateLimit($bw);

            $pool = Pool::where('pool_name', $plan->pool)->first();
            $localAddr = $pool && !empty($pool->local_ip) ? $pool->local_ip : $plan->pool;
            $remoteAddr = $pool ? $pool->pool_name : $plan->pool;

            $this->client->findOrCreate('/ppp/profile', 'name', $plan->name_plan, [
                'name' => $plan->name_plan,
                'local-address' => $localAddr,
                'remote-address' => $remoteAddr,
                'rate-limit' => $rate,
            ]);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe addPlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function updatePlan(Plan $oldPlan, Plan $newPlan): bool
    {
        try {
            $this->client->connect();

            $bw = Bandwidth::find($newPlan->id_bw);
            $rate = $this->computeRateLimit($bw);

            $pool = Pool::where('pool_name', $newPlan->pool)->first();
            $localAddr = $pool && !empty($pool->local_ip) ? $pool->local_ip : $newPlan->pool;
            $remoteAddr = $pool ? $pool->pool_name : $newPlan->pool;

            $this->client->findOrCreate('/ppp/profile', 'name', $oldPlan->name_plan, [
                'name' => $newPlan->name_plan,
                'local-address' => $localAddr,
                'remote-address' => $remoteAddr,
                'rate-limit' => $rate,
                'on-up' => $newPlan->on_login ?? '',
                'on-down' => $newPlan->on_logout ?? '',
            ]);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe updatePlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function removePlan(Plan $plan): bool
    {
        try {
            $this->client->connect();
            $this->client->findAndRemove('/ppp/profile', 'name', $plan->name_plan);
            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe removePlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function isCustomerOnline(Customer $customer): bool
    {
        try {
            $this->client->connect();

            $username = !empty($customer->pppoe_username) ? $customer->pppoe_username : $customer->username;
            $response = $this->client->sendRequest(
                '/ppp active print',
                ['.proplist' => '.id'],
                "name={$username}"
            );

            $this->client->disconnect();

            foreach ($response as $sentence) {
                if (isset($sentence['=.id'])) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe isCustomerOnline failed: {$e->getMessage()}");
            return false;
        }
    }

    public function disconnectCustomer(Customer $customer): bool
    {
        try {
            $this->client->connect();
            $this->removeActivePpoe($customer);
            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikPppoe disconnectCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function connectCustomer(Customer $customer, string $ip, string $mac): bool
    {
        return false;
    }

    private function findPpoeSecretId(Customer $customer): ?string
    {
        $response = $this->client->sendRequest(
            '/ppp/secret print',
            ['.proplist' => '.id'],
            "name={$customer->username}"
        );

        foreach ($response as $sentence) {
            if (isset($sentence['=.id'])) {
                return $sentence['=.id'];
            }
        }

        if (!empty($customer->pppoe_username)) {
            $response = $this->client->sendRequest(
                '/ppp/secret print',
                ['.proplist' => '.id'],
                "name={$customer->pppoe_username}"
            );
            foreach ($response as $sentence) {
                if (isset($sentence['=.id'])) {
                    return $sentence['=.id'];
                }
            }
        }

        return null;
    }

    private function removeActivePpoe(Customer $customer): void
    {
        $names = [$customer->username];
        if (!empty($customer->pppoe_username)) {
            $names[] = $customer->pppoe_username;
        }

        foreach ($names as $name) {
            try {
                $response = $this->client->sendRequest(
                    '/ppp active print',
                    ['.proplist' => '.id'],
                    "name={$name}"
                );
                foreach ($response as $sentence) {
                    if (isset($sentence['=.id'])) {
                        $this->client->sendCommand('/ppp/active/remove', [
                            'numbers' => $sentence['=.id'],
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    private function computeRateLimit(?Bandwidth $bw): string
    {
        if (!$bw || ($bw->rate_up == 0 && $bw->rate_down == 0)) {
            return '';
        }

        $unitDown = $bw->rate_down_unit === 'Kbps' ? 'K' : 'M';
        $unitUp = $bw->rate_up_unit === 'Kbps' ? 'K' : 'M';
        $rate = $bw->rate_up . $unitUp . '/' . $bw->rate_down . $unitDown;

        if (!empty(trim($bw->burst))) {
            $rate .= ' ' . $bw->burst;
        }

        return $rate;
    }
}
