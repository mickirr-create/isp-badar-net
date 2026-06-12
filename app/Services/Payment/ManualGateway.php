<?php

namespace App\Services\Payment;

use App\Models\Transaction;

class ManualGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Manual';
    }

    public function getPaymentUrl(string $invoice, float $amount, string $description, array $params = []): ?string
    {
        return null;
    }

    public function handleCallback(array $payload): ?array
    {
        return null;
    }

    public function verifyPayment(string $invoice): bool
    {
        $transaction = Transaction::where('invoice', $invoice)->first();

        if (!$transaction) {
            return false;
        }

        if ($transaction->status === 'Paid') {
            return true;
        }

        $transaction->update(['status' => 'Paid']);

        return true;
    }
}
