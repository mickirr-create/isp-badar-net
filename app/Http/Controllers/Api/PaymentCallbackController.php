<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserRecharge;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function midtrans(Request $request): JsonResponse
    {
        try {
            $gateway = PaymentGatewayFactory::make('Midtrans');
            $result = $gateway->handleCallback($request->all());

            if (!$result) {
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $this->processPayment($result['invoice'], $result['status']);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Midtrans callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    public function xendit(Request $request): JsonResponse
    {
        try {
            $gateway = PaymentGatewayFactory::make('Xendit');
            $result = $gateway->handleCallback($request->all());

            if (!$result) {
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $this->processPayment($result['invoice'], $result['status']);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Xendit callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    public function tripay(Request $request): JsonResponse
    {
        try {
            $gateway = PaymentGatewayFactory::make('Tripay');
            $result = $gateway->handleCallback($request->all());

            if (!$result) {
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $this->processPayment($result['invoice'], $result['status']);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Tripay callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    private function processPayment(string $invoice, string $status): void
    {
        $transaction = Transaction::where('invoice', $invoice)->first();

        if (!$transaction) {
            Log::warning("Transaction not found for invoice: {$invoice}");
            return;
        }

        $transaction->update(['status' => $status]);

        if ($status === 'Paid') {
            $recharge = UserRecharge::where('username', $transaction->username)
                ->where('routers', $transaction->routers)
                ->where('status', 'on')
                ->first();

            if ($recharge) {
                $recharge->update(['status' => 'on']);
            }
        }
    }
}
