<?php

namespace App\Services\Network\Drivers;

use App\Models\Bandwidth;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Models\UserRecharge;
use App\Services\Network\MikrotikClient;
use App\Services\Network\NetworkDeviceAdapter;
use Illuminate\Support\Facades\Log;

class MikrotikHotspotDriver implements NetworkDeviceAdapter
{
    private MikrotikClient $client;

    public function __construct(private readonly Router $router)
    {
        $this->client = new MikrotikClient(
            $router->ip_address,
            8728,
            $router->username,
            $router->password
        );
    }

    public function addCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $this->client->connect();

            // Check if plan has an expired plan
            $isExp = Plan::where('plan_expired', $plan->id)->first();

            // Remove existing hotspot user
            $this->client->findAndRemove('/ip/hotspot/user', 'name', $customer->username);

            // Remove active session if switching from expired plan
            if ($isExp) {
                $this->removeActiveUser($customer->username);
            }

            // Add hotspot user with profile
            $this->addHotspotUser($customer, $plan);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot addCustomer failed: {$e->getMessage()}", [
                'customer' => $customer->username,
                'plan' => $plan->name_plan,
                'router' => $this->router->name,
            ]);
            return false;
        }
    }

    public function removeCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $this->client->connect();

            if (!empty($plan->plan_expired)) {
                $expiredPlan = Plan::find($plan->plan_expired);
                if ($expiredPlan) {
                    $this->addCustomer($customer, $expiredPlan);
                    $this->removeActiveUser($customer->username);
                    $this->client->disconnect();
                    return true;
                }
            }

            $this->client->findAndRemove('/ip/hotspot/user', 'name', $customer->username);
            $this->removeActiveUser($customer->username);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot removeCustomer failed: {$e->getMessage()}", [
                'customer' => $customer->username,
                'router' => $this->router->name,
            ]);
            return false;
        }
    }

    public function syncCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $this->client->connect();

            $recharge = UserRecharge::where('username', $customer->username)
                ->where('status', 'on')
                ->first();

            if ($recharge) {
                $response = $this->client->sendRequest(
                    '/ip/hotspot/user print',
                    ['.proplist' => '.id,limit-uptime,limit-bytes-total'],
                    "name={$customer->username}"
                );

                $id = null;
                $hasLimits = false;

                foreach ($response as $sentence) {
                    if (isset($sentence['=.id'])) {
                        $id = $sentence['=.id'];
                        $hasLimits = !empty($sentence['=limit-uptime']) || !empty($sentence['=limit-bytes-total']);
                    }
                }

                if ($id && $hasLimits) {
                    $this->client->sendCommand('/ip/hotspot/user/set', [
                        'numbers' => $id,
                        'profile' => $recharge->namebp,
                    ]);
                } else {
                    $this->addCustomer($customer, $plan);
                }
            } else {
                $this->addCustomer($customer, $plan);
            }

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot syncCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function addPlan(Plan $plan): bool
    {
        try {
            $this->client->connect();

            $bw = Bandwidth::find($plan->id_bw);
            $rate = $this->computeRateLimit($bw, $plan);

            $this->client->findOrCreate('/ip/hotspot/user/profile', 'name', $plan->name_plan, [
                'name' => $plan->name_plan,
                'shared-users' => $plan->shared_users ?? 1,
                'rate-limit' => $rate,
            ]);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot addPlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function updatePlan(Plan $oldPlan, Plan $newPlan): bool
    {
        try {
            $this->client->connect();

            $bw = Bandwidth::find($newPlan->id_bw);
            $rate = $this->computeRateLimit($bw, $newPlan);

            $this->client->findOrCreate('/ip/hotspot/user/profile', 'name', $oldPlan->name_plan, [
                'name' => $newPlan->name_plan,
                'shared-users' => $newPlan->shared_users ?? 1,
                'rate-limit' => $rate,
                'on-login' => $newPlan->on_login ?? '',
                'on-logout' => $newPlan->on_logout ?? '',
            ]);

            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot updatePlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function removePlan(Plan $plan): bool
    {
        try {
            $this->client->connect();
            $this->client->findAndRemove('/ip/hotspot/user/profile', 'name', $plan->name_plan);
            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot removePlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function isCustomerOnline(Customer $customer): bool
    {
        try {
            $this->client->connect();
            $response = $this->client->sendRequest(
                '/ip/hotspot/active print',
                ['.proplist' => '.id'],
                "user={$customer->username}"
            );
            $this->client->disconnect();

            foreach ($response as $sentence) {
                if (isset($sentence['=.id'])) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot isCustomerOnline failed: {$e->getMessage()}");
            return false;
        }
    }

    public function disconnectCustomer(Customer $customer): bool
    {
        try {
            $this->client->connect();
            $this->removeActiveUser($customer->username);
            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot disconnectCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function connectCustomer(Customer $customer, string $ip, string $mac): bool
    {
        try {
            $this->client->connect();
            $this->client->sendCommand('/ip/hotspot/active/login', [
                'user' => $customer->username,
                'password' => $customer->password,
                'ip' => $ip,
                'mac-address' => $mac,
            ]);
            $this->client->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot connectCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function throttleCustomer(Customer $customer, string $throttleProfile): bool
    {
        try {
            $this->client->connect();

            $response = $this->client->sendRequest(
                '/ip/hotspot/user print',
                ['.proplist' => '.id'],
                "name={$customer->username}"
            );

            foreach ($response as $sentence) {
                if (isset($sentence['=.id'])) {
                    $this->client->sendCommand('/ip/hotspot/user/set', [
                        'numbers' => $sentence['=.id'],
                        'profile' => $throttleProfile,
                    ]);
                    $this->client->disconnect();
                    return true;
                }
            }

            $this->client->disconnect();
            return false;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot throttleCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function restoreCustomer(Customer $customer, string $originalProfile): bool
    {
        try {
            $this->client->connect();

            $response = $this->client->sendRequest(
                '/ip/hotspot/user print',
                ['.proplist' => '.id'],
                "name={$customer->username}"
            );

            foreach ($response as $sentence) {
                if (isset($sentence['=.id'])) {
                    $this->client->sendCommand('/ip/hotspot/user/set', [
                        'numbers' => $sentence['=.id'],
                        'profile' => $originalProfile,
                    ]);
                    $this->client->disconnect();
                    return true;
                }
            }

            $this->client->disconnect();
            return false;
        } catch (\Throwable $e) {
            Log::error("MikrotikHotspot restoreCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    private function addHotspotUser(Customer $customer, Plan $plan): void
    {
        $args = [
            'name' => $customer->username,
            'profile' => $plan->name_plan,
            'password' => $customer->password,
            'comment' => $customer->fullname,
            'email' => $customer->email ?? '',
        ];

        if ($plan->typebp === 'Limited') {
            if ($plan->limit_type === 'Time_Limit') {
                $args['limit-uptime'] = $plan->time_unit === 'Hrs'
                    ? $plan->time_limit . ':00:00'
                    : '00:' . $plan->time_limit . ':00';
            } elseif ($plan->limit_type === 'Data_Limit') {
                $args['limit-bytes-total'] = $plan->data_unit === 'GB'
                    ? $plan->data_limit . '000000000'
                    : $plan->data_limit . '000000';
            } elseif ($plan->limit_type === 'Both_Limit') {
                $args['limit-uptime'] = $plan->time_unit === 'Hrs'
                    ? $plan->time_limit . ':00:00'
                    : '00:' . $plan->time_limit . ':00';
                $args['limit-bytes-total'] = $plan->data_unit === 'GB'
                    ? $plan->data_limit . '000000000'
                    : $plan->data_limit . '000000';
            }
        }

        $this->client->sendCommand('/ip/hotspot/user/add', $args);
    }

    private function removeActiveUser(string $username): void
    {
        try {
            $response = $this->client->sendRequest(
                '/ip/hotspot/active print',
                ['.proplist' => '.id'],
                "user={$username}"
            );
            foreach ($response as $sentence) {
                if (isset($sentence['=.id'])) {
                    $this->client->sendCommand('/ip/hotspot/active/remove', [
                        'numbers' => $sentence['=.id'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors when removing active user
        }
    }

    private function computeRateLimit(?Bandwidth $bw, Plan $plan): string
    {
        if (!$bw || ($bw->rate_up == 0 && $bw->rate_down == 0)) {
            return '';
        }

        $unitDown = $bw->rate_down_unit === 'Kbps' ? 'K' : 'M';
        $unitUp = $bw->rate_up_unit === 'Kbps' ? 'K' : 'M';
        $rate = $bw->rate_up . $unitUp . '/' . $bw->rate_down . $unitDown;

        if (!empty(trim($bw->burst))) {
            $rate .= ' ' . $bw->burst;
        }

        return $rate;
    }
}
