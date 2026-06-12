<?php

namespace App\Services;

use App\Jobs\SyncMikrotikCustomer;
use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\Router;
use App\Models\UserRecharge;
use App\Services\Network\NetworkDeviceFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BillingCycleService
{
    /**
     * Get due date for a customer in current month
     */
    public function getDueDate(Customer $customer): ?Carbon
    {
        if (!$customer->billing_day) {
            return null;
        }

        $now = Carbon::now();
        $dueDate = $now->copy()->day($customer->billing_day);

        // If billing_day already passed this month, due date is next month
        if ($dueDate->lt($now)) {
            $dueDate->addMonth();
        }

        return $dueDate;
    }

    /**
     * Check if customer is due soon (within N days before due date)
     */
    public function isDueSoon(Customer $customer): bool
    {
        $dueDate = $this->getDueDate($customer);
        if (!$dueDate) {
            return false;
        }

        $notifyDays = (int) AppConfig::getSetting('billing_notify_days_before', 7);
        $daysUntilDue = (int) Carbon::now()->diffInDays($dueDate, false);

        return $daysUntilDue >= 0 && $daysUntilDue <= $notifyDays;
    }

    /**
     * Check if customer is overdue (past due date)
     */
    public function isOverdue(Customer $customer): bool
    {
        $dueDate = $this->getDueDate($customer);
        if (!$dueDate) {
            return false;
        }

        return $dueDate->isPast();
    }

    /**
     * Apply throttle to customer
     */
    public function applyThrottle(UserRecharge $recharge): bool
    {
        $customer = $recharge->customer;
        if (!$customer || !$customer->throttle_enabled) {
            return false;
        }

        $throttleProfile = $customer->throttle_profile
            ?? AppConfig::getSetting('billing_throttle_profile_hotspot', 'throttle-256k');

        $router = Router::where('name', $recharge->routers)->first();
        if (!$router) {
            Log::error("Router not found for recharge: {$recharge->routers}");
            return false;
        }

        try {
            $driver = NetworkDeviceFactory::make($router);
            $result = $driver->throttleCustomer($customer, $throttleProfile);

            if ($result) {
                $recharge->update([
                    'throttle_applied' => true,
                    'last_throttle_check' => Carbon::today(),
                ]);

                Log::info("Throttle applied to {$customer->username} with profile {$throttleProfile}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error("Failed to apply throttle to {$customer->username}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Restore customer speed after payment
     */
    public function restoreSpeed(UserRecharge $recharge): bool
    {
        $customer = $recharge->customer;
        if (!$customer) {
            return false;
        }

        $originalProfile = $recharge->plan->name_plan;

        $router = Router::where('name', $recharge->routers)->first();
        if (!$router) {
            Log::error("Router not found for recharge: {$recharge->routers}");
            return false;
        }

        try {
            $driver = NetworkDeviceFactory::make($router);
            $result = $driver->restoreCustomer($customer, $originalProfile);

            if ($result) {
                $recharge->update([
                    'throttle_applied' => false,
                    'last_throttle_check' => null,
                ]);

                Log::info("Speed restored for {$customer->username} to profile {$originalProfile}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error("Failed to restore speed for {$customer->username}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Process all overdue recharges - apply throttle
     */
    public function processOverdueRecharges(): array
    {
        $results = ['throttled' => 0, 'failed' => 0];

        $overdueRecharges = UserRecharge::overdue()
            ->where('type', '!=', 'Balance')
            ->with(['customer', 'plan'])
            ->get();

        foreach ($overdueRecharges as $recharge) {
            if (!$recharge->customer || !$recharge->customer->throttle_enabled) {
                continue;
            }

            // Check if already checked today
            if ($recharge->last_throttle_check && $recharge->last_throttle_check->isToday()) {
                continue;
            }

            $result = $this->applyThrottle($recharge);
            if ($result) {
                $results['throttled']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Process all throttled recharges - restore if paid
     */
    public function processThrottledRecharges(): array
    {
        $results = ['restored' => 0, 'failed' => 0];

        $throttledRecharges = UserRecharge::throttled()
            ->with(['customer', 'plan'])
            ->get();

        foreach ($throttledRecharges as $recharge) {
            // Check if customer has made payment recently (within 24 hours)
            $recentPayment = \App\Models\Transaction::where('user_id', $recharge->customer_id)
                ->where('recharged_on', '>=', Carbon::today())
                ->exists();

            if ($recentPayment) {
                $result = $this->restoreSpeed($recharge);
                if ($result) {
                    $results['restored']++;
                } else {
                    $results['failed']++;
                }
            }
        }

        return $results;
    }

    /**
     * Get all customers with due soon status
     */
    public function getDueSoonCustomers()
    {
        return Customer::where('billing_day', '>=', 1)
            ->where('throttle_enabled', true)
            ->whereHas('recharges', function ($q) {
                $q->where('status', 'on');
            })
            ->get()
            ->filter(function ($customer) {
                return $this->isDueSoon($customer);
            });
    }

    /**
     * Get billing cycle status for customer
     */
    public function getBillingStatus(Customer $customer): string
    {
        if (!$customer->billing_day) {
            return 'no_cycle';
        }

        $dueDate = $this->getDueDate($customer);
        if (!$dueDate) {
            return 'no_cycle';
        }

        $now = Carbon::now();
        $notifyDays = (int) AppConfig::getSetting('billing_notify_days_before', 7);
        $daysUntilDue = (int) $now->diffInDays($dueDate, false);

        if ($dueDate->isPast()) {
            return 'overdue';
        } elseif ($daysUntilDue <= $notifyDays) {
            return 'due_soon';
        } else {
            return 'active';
        }
    }
}
