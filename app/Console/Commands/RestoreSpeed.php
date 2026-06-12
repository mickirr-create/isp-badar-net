<?php

namespace App\Console\Commands;

use App\Services\BillingCycleService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class RestoreSpeed extends Command
{
    protected $signature = 'billing:restore-speed';
    protected $description = 'Restore speed for customers who have paid';

    public function handle(BillingCycleService $billingCycle, NotificationService $notification): int
    {
        $this->info('Checking throttled customers for payment...');

        $results = $billingCycle->processThrottledRecharges();

        $this->info("Done. Restored: {$results['restored']}, Failed: {$results['failed']}");

        return Command::SUCCESS;
    }
}
