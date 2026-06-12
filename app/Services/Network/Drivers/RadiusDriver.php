<?php

namespace App\Services\Network\Drivers;

use App\Models\Customer;
use App\Models\CustomerField;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Network\NetworkDeviceAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusDriver implements NetworkDeviceAdapter
{
    public function __construct(private readonly Router $router) {}

    public function addCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $conn = $this->getConnection();

            $password = $plan->type === 'PPPOE' && !empty($customer->pppoe_password)
                ? $customer->pppoe_password
                : $customer->password;

            $this->upsertRadcheck($conn, $customer->username, 'Cleartext-Password', $password);
            $this->upsertRadcheck($conn, $customer->username, 'Simultaneous-Use', $plan->type === 'PPPOE' ? '1' : ($plan->shared_users ?? '1'));
            $this->upsertRadcheck($conn, $customer->username, 'Port-Limit', $plan->type === 'PPPOE' ? '1' : ($plan->shared_users ?? '1'));
            $this->upsertRadcheck($conn, $customer->username, 'Mikrotik-Wireless-Comment', $customer->fullname);

            $this->upsertUserGroup($conn, $customer->username, 'plan_' . $plan->id);

            $this->deleteRadcheckAttr($conn, $customer->username, 'Max-All-Session');
            $this->deleteRadcheckAttr($conn, $customer->username, 'Max-Data');
            $this->deleteRadcheckAttr($conn, $customer->username, 'Mikrotik-Rate-Limit');
            $this->deleteRadcheckAttr($conn, $customer->username, 'WISPr-Session-Terminate-Time');

            if ($plan->type === 'Hotspot' && $plan->typebp === 'Limited') {
                $this->applyHotspotLimits($conn, $customer, $plan);
            }

            $this->addBandwidth($conn, $customer, $plan);

            if ($plan->type === 'PPPOE') {
                $this->upsertRadreply($conn, $customer->username, 'Framed-Pool', $plan->pool);
                $ip = !empty($customer->pppoe_ip) ? $customer->pppoe_ip : '0.0.0.0';
                $this->upsertRadreply($conn, $customer->username, 'Framed-IP-Address', $ip);
                $this->upsertRadreply($conn, $customer->username, 'Framed-IP-Netmask', '255.255.255.0');
            }

            $this->disconnectCustomer($customer);
            $conn->table('radacct')->where('username', $customer->username)->delete();

            return true;
        } catch (\Throwable $e) {
            Log::error("Radius addCustomer failed: {$e->getMessage()}", [
                'customer' => $customer->username,
                'plan' => $plan->name_plan,
            ]);
            return false;
        }
    }

    public function removeCustomer(Customer $customer, Plan $plan): bool
    {
        try {
            $conn = $this->getConnection();

            if (!empty($plan->plan_expired)) {
                $expiredPlan = Plan::find($plan->plan_expired);
                if ($expiredPlan) {
                    return $this->addCustomer($customer, $expiredPlan);
                }
            }

            $randomPass = md5(time() . $customer->username);
            $this->upsertRadcheck($conn, $customer->username, 'Cleartext-Password', $randomPass);
            $this->disconnectCustomer($customer);

            return true;
        } catch (\Throwable $e) {
            Log::error("Radius removeCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function syncCustomer(Customer $customer, Plan $plan): bool
    {
        return $this->addCustomer($customer, $plan);
    }

    public function addPlan(Plan $plan): bool
    {
        try {
            $conn = $this->getConnection();
            $this->addPlanBandwidth($conn, $plan);
            return true;
        } catch (\Throwable $e) {
            Log::error("Radius addPlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function updatePlan(Plan $oldPlan, Plan $newPlan): bool
    {
        return $this->addPlan($newPlan);
    }

    public function removePlan(Plan $plan): bool
    {
        try {
            $conn = $this->getConnection();
            $conn->table('radgroupreply')->where('plan_id', $plan->id)->delete();
            $conn->table('radusergroup')->where('groupname', 'plan_' . $plan->id)->delete();
            return true;
        } catch (\Throwable $e) {
            Log::error("Radius removePlan failed: {$e->getMessage()}");
            return false;
        }
    }

    public function isCustomerOnline(Customer $customer): bool
    {
        try {
            $conn = $this->getConnection();
            return $conn->table('radacct')
                ->where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function disconnectCustomer(Customer $customer): bool
    {
        try {
            $conn = $this->getConnection();
            $active = $conn->table('radacct')
                ->where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->first();

            if (!$active) return true;

            $nas = $conn->table('nas')
                ->where('nasname', $active->nasipaddress)
                ->get();

            foreach ($nas as $n) {
                $port = !empty($n->ports) ? $n->ports : 3799;
                @shell_exec("echo 'User-Name = {$customer->username},Framed-IP-Address = {$active->framedipaddress}' | radclient -x " . trim($n->nasname) . ":{$port} disconnect '" . $n->secret . "'");
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Radius disconnectCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function connectCustomer(Customer $customer, string $ip, string $mac): bool
    {
        return false;
    }

    public function throttleCustomer(Customer $customer, string $throttleProfile): bool
    {
        try {
            $conn = $this->getConnection();

            // Update rate limit to throttle profile
            $this->upsertRadcheck($conn, $customer->username, 'Mikrotik-Rate-Limit', $throttleProfile);

            $this->disconnectCustomer($customer);
            return true;
        } catch (\Throwable $e) {
            Log::error("Radius throttleCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    public function restoreCustomer(Customer $customer, string $originalProfile): bool
    {
        try {
            $conn = $this->getConnection();

            // Restore original rate limit
            $this->upsertRadcheck($conn, $customer->username, 'Mikrotik-Rate-Limit', $originalProfile);

            $this->disconnectCustomer($customer);
            return true;
        } catch (\Throwable $e) {
            Log::error("Radius restoreCustomer failed: {$e->getMessage()}");
            return false;
        }
    }

    private function getConnection()
    {
        try {
            return DB::connection('radius');
        } catch (\Throwable $e) {
            return DB::connection();
        }
    }

    private function upsertRadcheck($conn, string $username, string $attribute, string $value, string $op = ':='): void
    {
        $existing = $conn->table('radcheck')
            ->where('username', $username)
            ->where('attribute', $attribute)
            ->first();

        if ($existing) {
            $conn->table('radcheck')
                ->where('id', $existing->id)
                ->update(['value' => $value, 'op' => $op]);
        } else {
            $conn->table('radcheck')->insert([
                'username' => $username,
                'attribute' => $attribute,
                'op' => $op,
                'value' => $value,
            ]);
        }
    }

    private function upsertRadreply($conn, string $username, string $attribute, string $value, string $op = ':='): void
    {
        $existing = $conn->table('radreply')
            ->where('username', $username)
            ->where('attribute', $attribute)
            ->first();

        if ($existing) {
            $conn->table('radreply')
                ->where('id', $existing->id)
                ->update(['value' => $value, 'op' => $op]);
        } else {
            $conn->table('radreply')->insert([
                'username' => $username,
                'attribute' => $attribute,
                'op' => $op,
                'value' => $value,
            ]);
        }
    }

    private function upsertUserGroup($conn, string $username, string $groupname): void
    {
        $existing = $conn->table('radusergroup')
            ->where('username', $username)
            ->first();

        if ($existing) {
            $conn->table('radusergroup')
                ->where('username', $username)
                ->update(['groupname' => $groupname]);
        } else {
            $conn->table('radusergroup')->insert([
                'username' => $username,
                'groupname' => $groupname,
                'priority' => 1,
            ]);
        }
    }

    private function deleteRadcheckAttr($conn, string $username, string $attribute): void
    {
        $conn->table('radcheck')
            ->where('username', $username)
            ->where('attribute', $attribute)
            ->delete();
    }

    private function applyHotspotLimits($conn, Customer $customer, Plan $plan): void
    {
        if ($plan->limit_type === 'Time_Limit') {
            $seconds = $plan->time_unit === 'Hrs'
                ? $plan->time_limit * 3600
                : $plan->time_limit * 60;
            $this->upsertRadcheck($conn, $customer->username, 'Max-All-Session', (string) $seconds);
        } elseif ($plan->limit_type === 'Data_Limit') {
            $bytes = $plan->data_unit === 'GB'
                ? $plan->data_limit . '000000000'
                : $plan->data_limit . '000000';
            $this->upsertRadcheck($conn, $customer->username, 'Max-Data', $bytes);
        } elseif ($plan->limit_type === 'Both_Limit') {
            $seconds = $plan->time_unit === 'Hrs'
                ? $plan->time_limit * 3600
                : $plan->time_limit * 60;
            $this->upsertRadcheck($conn, $customer->username, 'Max-All-Session', (string) $seconds);

            $bytes = $plan->data_unit === 'GB'
                ? $plan->data_limit . '000000000'
                : $plan->data_limit . '000000';
            $this->upsertRadcheck($conn, $customer->username, 'Max-Data', $bytes);
        }
    }

    private function addBandwidth($conn, Customer $customer, Plan $plan): void
    {
        $bw = \App\Models\Bandwidth::find($plan->id_bw);
        if (!$bw) return;

        $unitDown = $bw->rate_down_unit === 'Kbps' ? 'K' : 'M';
        $unitUp = $bw->rate_up_unit === 'Kbps' ? 'K' : 'M';
        $rateLimit = $bw->rate_up . $unitUp . '/' . $bw->rate_down . $unitDown;

        if (!empty(trim($bw->burst))) {
            $rateLimit .= ' ' . $bw->burst;
        }

        $this->upsertRadcheck($conn, $customer->username, 'Mikrotik-Rate-Limit', $rateLimit);
    }

    private function addPlanBandwidth($conn, Plan $plan): void
    {
        $bw = \App\Models\Bandwidth::find($plan->id_bw);
        if (!$bw) return;

        $unitDown = $bw->rate_down_unit === 'Kbps' ? 'K' : 'M';
        $unitUp = $bw->rate_up_unit === 'Kbps' ? 'K' : 'M';
        $rates = [$bw->rate_up . $unitUp, $bw->rate_down . $unitDown];

        $rateLimit = $rates[0] . '/' . $rates[1];
        if (!empty(trim($bw->burst))) {
            $rateLimit .= ' ' . $bw->burst;
        }

        $this->upsertGroupReply($conn, $plan->id, 'Ascend-Data-Rate', (string) $this->stringToInteger($rates[1]));
        $this->upsertGroupReply($conn, $plan->id, 'Ascend-Xmit-Rate', (string) $this->stringToInteger($rates[0]));
        $this->upsertGroupReply($conn, $plan->id, 'Mikrotik-Rate-Limit', $rateLimit);
    }

    private function upsertGroupReply($conn, int $planId, string $attribute, string $value, string $op = ':='): void
    {
        $existing = $conn->table('radgroupreply')
            ->where('plan_id', $planId)
            ->where('attribute', $attribute)
            ->first();

        if ($existing) {
            $conn->table('radgroupreply')
                ->where('id', $existing->id)
                ->update(['value' => $value, 'op' => $op]);
        } else {
            $conn->table('radgroupreply')->insert([
                'groupname' => 'plan_' . $planId,
                'plan_id' => $planId,
                'attribute' => $attribute,
                'op' => $op,
                'value' => $value,
            ]);
        }
    }

    private function stringToInteger(string $str): int
    {
        return (int) str_replace('G', '000000000', str_replace('M', '000000', str_replace('K', '000', $str)));
    }
}
