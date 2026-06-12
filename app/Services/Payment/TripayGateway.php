<?php

namespace App\Services\Payment;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayGateway implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $privateKey;
    private string $merchantCode;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = AppConfig::getSetting('tripay_api_key', '');
        $this->privateKey = AppConfig::getSetting('tripay_private_key', '');
        $this->merchantCode = AppConfig::getSetting('tripay_merchant_code', '');
        $this->baseUrl = 'https://tripay.co.id/api';
    }

    public function getName(): string
    {
        return 'Tripay';
    }

    public function getPaymentUrl(string $invoice, float $amount, string $description, array $params = []): ?string
    {
        try {
            $signature = hash_hmac('sha256', $this->merchantCode . $invoice . (int) $amount, $this->privateKey);

            $payload = [
                'method' => $params['payment_method'] ?? 'BRIVA',
                'merchant_code' => $this->merchantCode,
                'merchant_ref' => $invoice,
                'amount' => (int) $amount,
                'customer_name' => $params['customer_name'] ?? '',
                'customer_email' => $params['email'] ?? '',
                'customer_phone' => $params['phone'] ?? '',
                'callback_url' => $params['callback_url'] ?? '',
                'return_url' => $params['return_url'] ?? route('customer.dashboard'),
                'signature' => $signature,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/transaction/create", $payload);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data');
                return $data['checkout_url'] ?? null;
            }

            Log::error('Tripay payment creation failed', [
                'invoice' => $invoice,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay payment error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleCallback(array $payload): ?array
    {
        try {
            $status = $payload['status'] ?? '';
            $invoice = $payload['merchant_ref'] ?? '';

            $statusMap = [
                'PAID' => 'Paid',
                'EXPIRED' => 'Failed',
                'PENDING' => 'Pending',
                'REFUND' => 'Refunded',
            ];

            return [
                'invoice' => $invoice,
                'status' => $statusMap[$status] ?? 'Unknown',
                'gateway_response' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error('Tripay callback error: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyPayment(string $invoice): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get("{$this->baseUrl}/transaction/detail/{$invoice}");

            if ($response->successful() && $response->json('success')) {
                $status = $response->json('data.status') ?? '';
                return $status === 'PAID';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Tripay verification error: ' . $e->getMessage());
            return false;
        }
    }
}
