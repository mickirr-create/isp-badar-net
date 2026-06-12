<?php

namespace App\Services\Network;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;

interface NetworkDeviceAdapter
{
    public function __construct(Router $router);
    public function addCustomer(Customer $customer, Plan $plan): bool;
    public function removeCustomer(Customer $customer, Plan $plan): bool;
    public function syncCustomer(Customer $customer, Plan $plan): bool;
    public function addPlan(Plan $plan): bool;
    public function updatePlan(Plan $oldPlan, Plan $newPlan): bool;
    public function removePlan(Plan $plan): bool;
    public function isCustomerOnline(Customer $customer): bool;
    public function disconnectCustomer(Customer $customer): bool;
    public function connectCustomer(Customer $customer, string $ip, string $mac): bool;
    public function throttleCustomer(Customer $customer, string $throttleProfile): bool;
    public function restoreCustomer(Customer $customer, string $originalProfile): bool;
}
