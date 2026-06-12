<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    public function getName(): string;

    public function getPaymentUrl(string $invoice, float $amount, string $description, array $params = []): ?string;

    public function handleCallback(array $payload): ?array;

    public function verifyPayment(string $invoice): bool;
}
