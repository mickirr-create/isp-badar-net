<?php

namespace App\Services\Payment;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransGateway implements PaymentGatewayInterface
{
    private string $serverKey;
    private string $clientKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->serverKey = AppConfig::getSetting('midtrans_server_key', '');
        $this->clientKey = AppConfig::getSetting('midtrans_client_key', '');
        $this->baseUrl = AppConfig::getSetting('midtrans_production', 'false') === 'true'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    public function getName(): string
    {
        return 'Midtrans';
    }

    public function getPaymentUrl(string $invoice, float $amount, string $description, array $params = []): ?string
    {
        try {
            $payload = [
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => (int) $amount,
                ],
                'customer_details' => [
                    'first_name' => $params['customer_name'] ?? '',
                    'email' => $params['email'] ?? '',
                    'phone' => $params['phone'] ?? '',
                ],
                'item_details' => [
                    [
                        'id' => $params['plan_id'] ?? 'plan',
                        'price' => (int) $amount,
                        'quantity' => 1,
                        'name' => $description,
                    ],
                ],
                'callbacks' => [
                    'finish' => $params['callback_url'] ?? route('customer.dashboard'),
                ],
            ];

            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v2/charge", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['redirect_url'] ?? null;
            }

            Log::error('Midtrans payment creation failed', [
                'invoice' => $invoice,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans payment error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleCallback(array $payload): ?array
    {
        try {
            $statusCode = $payload['status_code'] ?? '';
            $transactionStatus = $payload['transaction_status'] ?? '';
            $invoice = $payload['order_id'] ?? '';

            $statusMap = [
                'capture' => 'Paid',
                'settlement' => 'Paid',
                'pending' => 'Pending',
                'deny' => 'Failed',
                'cancel' => 'Failed',
                'expire' => 'Failed',
                'refund' => 'Refunded',
            ];

            $status = $statusMap[$transactionStatus] ?? 'Unknown';

            return [
                'invoice' => $invoice,
                'status' => $status,
                'gateway_response' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans callback error: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyPayment(string $invoice): bool
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get("{$this->baseUrl}/v2/{$invoice}/status");

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['transaction_status'] ?? '';
                return in_array($status, ['capture', 'settlement']);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Midtrans verification error: ' . $e->getMessage());
            return false;
        }
    }
}
