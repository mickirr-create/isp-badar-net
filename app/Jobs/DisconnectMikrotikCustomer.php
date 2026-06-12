<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Router;
use App\Services\Network\NetworkDeviceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DisconnectMikrotikCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public int $customerId,
        public string $routerName
    ) {}

    public function handle(): void
    {
        $customer = Customer::findOrFail($this->customerId);
        $router = Router::where('name', $this->routerName)->firstOrFail();

        $driver = NetworkDeviceFactory::make($router);
        $driver->disconnectCustomer($customer);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("MikroTik DisconnectMikrotikCustomer job failed: {$exception->getMessage()}", [
            'customer_id' => $this->customerId,
            'router' => $this->routerName,
        ]);
    }
}
