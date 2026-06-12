<?php

namespace App\Console\Commands;

use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\UserRecharge;
use App\Services\BillingCycleService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDueDates extends Command
{
    protected $signature = 'billing:check-due-dates';
    protected $description = 'Check due dates and send notifications to customers';

    public function handle(BillingCycleService $billingCycle, NotificationService $notification): int
    {
        $this->info('Checking due dates...');

        $customers = Customer::where('billing_day', '>=', 1)
            ->where('throttle_enabled', true)
            ->where('status', 'Active')
            ->with(['recharges' => function ($q) {
                $q->where('status', 'on')
                  ->where('expiration', '>=', Carbon::today());
            }])
            ->get();

        $notified = 0;

        foreach ($customers as $customer) {
            if ($customer->recharges->isEmpty()) {
                continue;
            }

            if ($billingCycle->isDueSoon($customer)) {
                foreach ($customer->recharges as $recharge) {
                    $notification->sendDueSoonNotification($recharge);
                    $notified++;
                    $this->line("  Notified: {$customer->username} (due: {$customer->due_date->format('d/m/Y')})");
                }
            }
        }

        $this->info("Done. Notified {$notified} customers.");
        return Command::SUCCESS;
    }
}
