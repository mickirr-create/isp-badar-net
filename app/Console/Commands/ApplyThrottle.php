<?php

namespace App\Console\Commands;

use App\Services\BillingCycleService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ApplyThrottle extends Command
{
    protected $signature = 'billing:apply-throttle';
    protected $description = 'Apply throttle to overdue customers';

    public function handle(BillingCycleService $billingCycle, NotificationService $notification): int
    {
        $this->info('Applying throttle to overdue customers...');

        $results = $billingCycle->processOverdueRecharges();

        $this->info("Done. Throttled: {$results['throttled']}, Failed: {$results['failed']}");

        return Command::SUCCESS;
    }
}
