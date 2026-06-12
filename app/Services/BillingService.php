<?php

namespace App\Services;

use App\Jobs\SyncMikrotikCustomer;
use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\CustomerField;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\UserRecharge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    public function recharge(
        int $customerId,
        string $routerName,
        int $planId,
        string $gateway = 'Manual',
        string $channel = '',
        ?int $adminId = null,
        string $note = ''
    ): ?string {
        return DB::transaction(function () use ($customerId, $routerName, $planId, $gateway, $channel, $adminId, $note) {
            $dateOnly = date('Y-m-d');
            $timeOnly = date('H:i:s');
            $time = date('H:i:s');

            $plan = Plan::find($planId);
            if (!$plan) {
                Log::error("Plan not found: {$planId}");
                return null;
            }

            $customer = Customer::find($customerId);
            if (!$customer) {
                Log::error("Customer not found: {$customerId}");
                return null;
            }

            if ($customer->status !== 'Active') {
                Log::warning("Customer {$customer->username} is not active: {$customer->status}");
                return null;
            }

            $extendExpiry = AppConfig::getSetting('extend_expiry', 'yes') === 'yes';

            $expiration = $this->calculateExpiration($plan, $extendExpiry ? $customer->id : null, $routerName);

            $existingRecharge = UserRecharge::where('customer_id', $customerId)
                ->where('routers', $routerName)
                ->where('type', $plan->type)
                ->where('status', 'on')
                ->first();

            $invoice = $this->generateInvoice();

            if ($existingRecharge) {
                $samePlan = $existingRecharge->plan_id == $planId;

                if ($samePlan && $extendExpiry) {
                    $expiration = $this->extendExpiration($existingRecharge, $plan);
                }

                $existingRecharge->update([
                    'plan_id' => $planId,
                    'namebp' => $plan->name_plan,
                    'recharged_on' => $dateOnly,
                    'recharged_time' => $timeOnly,
                    'expiration' => $expiration['date'],
                    'time' => $expiration['time'],
                    'status' => 'on',
                    'method' => "{$gateway} - {$channel}",
                    'admin_id' => $adminId ?? 0,
                ]);
            } else {
                UserRecharge::create([
                    'customer_id' => $customerId,
                    'username' => $customer->username,
                    'plan_id' => $planId,
                    'namebp' => $plan->name_plan,
                    'recharged_on' => $dateOnly,
                    'recharged_time' => $timeOnly,
                    'expiration' => $expiration['date'],
                    'time' => $expiration['time'],
                    'status' => 'on',
                    'method' => "{$gateway} - {$channel}",
                    'routers' => $routerName,
                    'type' => $plan->type,
                    'admin_id' => $adminId ?? 0,
                ]);
            }

            Transaction::create([
                'invoice' => $invoice,
                'username' => $customer->username,
                'user_id' => $customerId,
                'plan_name' => $plan->name_plan,
                'price' => $plan->price,
                'recharged_on' => $dateOnly,
                'recharged_time' => $timeOnly,
                'expiration' => $expiration['date'],
                'time' => $expiration['time'],
                'method' => "{$gateway} - {$channel}",
                'routers' => $routerName,
                'type' => $plan->type,
                'note' => $note,
                'admin_id' => $adminId ?? 0,
            ]);

            if ($plan->validity_unit === 'Period' && $plan->price != 0) {
                CustomerField::updateOrCreate(
                    ['customer_id' => $customerId, 'field_name' => 'Invoice'],
                    ['field_value' => $plan->price]
                );
            }

            if (in_array($plan->device, ['MikrotikHotspot', 'MikrotikPppoe', 'Radius'])) {
                SyncMikrotikCustomer::dispatch($customerId, $planId, $routerName, 'add');
            }

            return $invoice;
        });
    }

    public function rechargeBalance(
        int $customerId,
        int $planId,
        string $gateway = 'Manual',
        string $channel = '',
        ?int $adminId = null,
        string $note = ''
    ): ?string {
        return DB::transaction(function () use ($customerId, $planId, $gateway, $channel, $adminId, $note) {
            $customer = Customer::find($customerId);
            $plan = Plan::find($planId);

            if (!$customer || !$plan) {
                return null;
            }

            $invoice = $this->generateInvoice();

            Transaction::create([
                'invoice' => $invoice,
                'username' => $customer->username,
                'user_id' => $customerId,
                'plan_name' => $plan->name_plan,
                'price' => $plan->price,
                'recharged_on' => date('Y-m-d'),
                'recharged_time' => date('H:i:s'),
                'expiration' => date('Y-m-d'),
                'time' => date('H:i:s'),
                'method' => "{$gateway} - {$channel}",
                'routers' => 'balance',
                'type' => 'Balance',
                'note' => $note,
                'admin_id' => $adminId ?? 0,
            ]);

            $customer->increment('balance', $plan->price);

            return $invoice;
        });
    }

    private function calculateExpiration(Plan $plan, ?int $customerId = null, ?string $routerName = null): array
    {
        $dateOnly = date('Y-m-d');
        $time = date('H:i:s');

        $dayExp = 20;
        if ($plan->validity_unit === 'Period') {
            if ($customerId) {
                $field = CustomerField::where('customer_id', $customerId)
                    ->where('field_name', 'Expired Date')
                    ->first();
                $dayExp = $field ? (int) $field->field_value : ($plan->expired_date ?: 20);
            } else {
                $dayExp = $plan->expired_date ?: 20;
            }
        }

        switch ($plan->validity_unit) {
            case 'Months':
                $dateExp = date('Y-m-d', strtotime("+{$plan->validity} month"));
                break;

            case 'Period':
                $currentDate = new \DateTime($dateOnly);
                $expDate = clone $currentDate;
                $expDate->modify('first day of next month');
                $expDate->setDate((int)$expDate->format('Y'), (int)$expDate->format('m'), $dayExp);

                $minDays = 7 * $plan->validity;
                $maxDays = 35 * $plan->validity;
                $daysUntilExp = $expDate->diff($currentDate)->days;

                while ($daysUntilExp < $minDays) {
                    $expDate->modify('+1 month');
                    $daysUntilExp = $expDate->diff($currentDate)->days;
                }
                while ($daysUntilExp > $maxDays) {
                    $expDate->modify('-1 month');
                    $daysUntilExp = $expDate->diff($currentDate)->days;
                }
                if ($daysUntilExp < $minDays || $expDate <= $currentDate) {
                    $expDate->modify('+1 month');
                }
                if ($plan->validity > 1) {
                    $expDate->modify('+' . ($plan->validity - 1) . ' months');
                }

                $dateExp = $expDate->format('Y-m-d');
                $time = '23:59:59';
                break;

            case 'Days':
                $datetime = explode(' ', date('Y-m-d H:i:s', strtotime("+{$plan->validity} day")));
                $dateExp = $datetime[0];
                $time = $datetime[1];
                break;

            case 'Hrs':
                $datetime = explode(' ', date('Y-m-d H:i:s', strtotime("+{$plan->validity} hour")));
                $dateExp = $datetime[0];
                $time = $datetime[1];
                break;

            case 'Mins':
                $datetime = explode(' ', date('Y-m-d H:i:s', strtotime("+{$plan->validity} minute")));
                $dateExp = $datetime[0];
                $time = $datetime[1];
                break;

            default:
                $dateExp = date('Y-m-d', strtotime("+1 month"));
                break;
        }

        return ['date' => $dateExp, 'time' => $time];
    }

    private function extendExpiration(UserRecharge $recharge, Plan $plan): array
    {
        $currentExp = $recharge->expiration;
        $currentTime = $recharge->time;

        switch ($plan->validity_unit) {
            case 'Months':
                $dateExp = date('Y-m-d', strtotime("{$currentExp} +{$plan->validity} month"));
                $time = $currentTime;
                break;
            case 'Days':
                $dateExp = date('Y-m-d', strtotime("{$currentExp} +{$plan->validity} day"));
                $time = $currentTime;
                break;
            case 'Hrs':
                $datetime = explode(' ', date('Y-m-d H:i:s', strtotime("{$currentExp} {$currentTime} +{$plan->validity} hour")));
                $dateExp = $datetime[0];
                $time = $datetime[1];
                break;
            case 'Mins':
                $datetime = explode(' ', date('Y-m-d H:i:s', strtotime("{$currentExp} {$currentTime} +{$plan->validity} minute")));
                $dateExp = $datetime[0];
                $time = $datetime[1];
                break;
            default:
                $dateExp = $currentExp;
                $time = $currentTime;
                break;
        }

        return ['date' => $dateExp, 'time' => $time];
    }

    private function generateInvoice(): string
    {
        $maxId = Transaction::max('id') ?? 0;
        return 'INV-' . ($maxId + 1);
    }
}
