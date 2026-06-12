<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Network\NetworkDeviceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMikrotikCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public int $customerId,
        public int $planId,
        public string $routerName,
        public string $action = 'add'
    ) {}

    public function handle(): void
    {
        $customer = Customer::findOrFail($this->customerId);
        $plan = Plan::findOrFail($this->planId);
        $router = Router::where('name', $this->routerName)->firstOrFail();

        $driver = NetworkDeviceFactory::make($router, $plan);

        $result = match ($this->action) {
            'add' => $driver->addCustomer($customer, $plan),
            'remove' => $driver->removeCustomer($customer, $plan),
            'sync' => $driver->syncCustomer($customer, $plan),
            default => false,
        };

        if (!$result) {
            Log::warning("MikroTik sync action '{$this->action}' returned false", [
                'customer' => $customer->username,
                'plan' => $plan->name_plan,
                'router' => $this->routerName,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("MikroTik SyncMikrotikCustomer job failed: {$exception->getMessage()}", [
            'customer_id' => $this->customerId,
            'plan_id' => $this->planId,
            'router' => $this->routerName,
            'action' => $this->action,
        ]);
    }
}
