<?php

namespace App\Services\Payment;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = AppConfig::getSetting('xendit_secret_key', '');
        $this->apiKey = AppConfig::getSetting('xendit_api_key', '');
        $this->baseUrl = 'https://api.xendit.co';
    }

    public function getName(): string
    {
        return 'Xendit';
    }

    public function getPaymentUrl(string $invoice, float $amount, string $description, array $params = []): ?string
    {
        try {
            $payload = [
                'external_id' => $invoice,
                'amount' => (int) $amount,
                'description' => $description,
                'payer_email' => $params['email'] ?? '',
                'checkout_methods' => ['ONE_TIME', 'EWALLET', 'VIRTUAL_ACCOUNT'],
                'payment_methods' => ['BCA', 'BRI', 'BNI', 'MANDIRI', 'OVO', 'GOPAY', 'DANA', 'LINKAJA'],
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['invoice_url'] ?? null;
            }

            Log::error('Xendit payment creation failed', [
                'invoice' => $invoice,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Xendit payment error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleCallback(array $payload): ?array
    {
        try {
            $status = $payload['status'] ?? '';
            $invoice = $payload['external_id'] ?? '';

            $statusMap = [
                'PAID' => 'Paid',
                'EXPIRED' => 'Failed',
                'PENDING' => 'Pending',
            ];

            return [
                'invoice' => $invoice,
                'status' => $statusMap[$status] ?? 'Unknown',
                'gateway_response' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error('Xendit callback error: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyPayment(string $invoice): bool
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/v2/invoices/{$invoice}");

            if ($response->successful()) {
                $data = $response->json();
                return ($data['status'] ?? '') === 'PAID';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Xendit verification error: ' . $e->getMessage());
            return false;
        }
    }
}
