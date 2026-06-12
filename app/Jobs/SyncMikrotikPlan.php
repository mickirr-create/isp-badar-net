<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Models\Router;
use App\Services\Network\NetworkDeviceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMikrotikPlan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public int $planId,
        public string $routerName,
        public string $action = 'add',
        public ?int $oldPlanId = null
    ) {}

    public function handle(): void
    {
        $plan = Plan::findOrFail($this->planId);
        $router = Router::where('name', $this->routerName)->firstOrFail();

        $driver = NetworkDeviceFactory::make($router, $plan);

        $result = match ($this->action) {
            'add' => $driver->addPlan($plan),
            'remove' => $driver->removePlan($plan),
            'update' => $driver->updatePlan(
                $this->oldPlanId ? Plan::findOrFail($this->oldPlanId) : $plan,
                $plan
            ),
            default => false,
        };

        if (!$result) {
            Log::warning("MikroTik plan sync action '{$this->action}' returned false", [
                'plan' => $plan->name_plan,
                'router' => $this->routerName,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("MikroTik SyncMikrotikPlan job failed: {$exception->getMessage()}", [
            'plan_id' => $this->planId,
            'router' => $this->routerName,
            'action' => $this->action,
        ]);
    }
}
