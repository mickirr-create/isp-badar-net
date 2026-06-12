<?php

namespace App\Services\Network\Drivers;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Network\NetworkDeviceAdapter;

class DummyDriver implements NetworkDeviceAdapter
{
    public function __construct(private readonly Router $router) {}

    public function addCustomer(Customer $customer, Plan $plan): bool { return true; }
    public function removeCustomer(Customer $customer, Plan $plan): bool { return true; }
    public function syncCustomer(Customer $customer, Plan $plan): bool { return true; }
    public function addPlan(Plan $plan): bool { return true; }
    public function updatePlan(Plan $oldPlan, Plan $newPlan): bool { return true; }
    public function removePlan(Plan $plan): bool { return true; }
    public function isCustomerOnline(Customer $customer): bool { return false; }
    public function disconnectCustomer(Customer $customer): bool { return true; }
    public function connectCustomer(Customer $customer, string $ip, string $mac): bool { return true; }
}
