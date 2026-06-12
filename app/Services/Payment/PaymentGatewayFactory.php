<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class PaymentGatewayFactory
{
    private static array $gateways = [
        'Manual' => ManualGateway::class,
        'Midtrans' => MidtransGateway::class,
        'Xendit' => XenditGateway::class,
        'Tripay' => TripayGateway::class,
    ];

    public static function make(string $name): PaymentGatewayInterface
    {
        $gatewayClass = self::$gateways[$name] ?? null;

        if (!$gatewayClass) {
            Log::warning("Payment gateway '{$name}' not found, falling back to Manual");
            return new ManualGateway();
        }

        return new $gatewayClass();
    }

    public static function available(): array
    {
        return array_keys(self::$gateways);
    }
}
