<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function generate(
        int $planId,
        string $routerName,
        int $quantity = 1,
        ?int $adminId = null
    ): array {
        $vouchers = [];

        DB::transaction(function () use ($planId, $routerName, $quantity, $adminId, &$vouchers) {
            $plan = \App\Models\Plan::find($planId);
            if (!$plan) {
                throw new \RuntimeException("Plan not found: {$planId}");
            }

            for ($i = 0; $i < $quantity; $i++) {
                $code = $this->generateCode();

                $vouchers[] = Voucher::create([
                    'type' => $plan->type,
                    'routers' => $routerName,
                    'id_plan' => $planId,
                    'code' => $code,
                    'user' => $code,
                    'status' => 'Available',
                    'generated_by' => $adminId ?? 0,
                ]);
            }
        });

        return $vouchers;
    }

    public function redeem(string $code, ?int $customerId = null): bool
    {
        return DB::transaction(function () use ($code, $customerId) {
            $voucher = Voucher::where('code', $code)
                ->where('status', 'Available')
                ->first();

            if (!$voucher) {
                return false;
            }

            $voucher->update([
                'status' => 'Used',
                'user' => $customerId ? (string) $customerId : $voucher->user,
                'used_date' => now(),
            ]);

            return true;
        });
    }

    public function getCode(string $code): ?Voucher
    {
        return Voucher::where('code', $code)->first();
    }

    public function generateCode(int $length = 8): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $code;
    }
}
