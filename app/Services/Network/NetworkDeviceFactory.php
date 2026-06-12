<?php

namespace App\Services\Network;

use App\Models\Plan;
use App\Models\Router;
use App\Services\Network\Drivers\DummyDriver;
use App\Services\Network\Drivers\MikrotikHotspotDriver;
use App\Services\Network\Drivers\MikrotikPppoeDriver;
use App\Services\Network\Drivers\RadiusDriver;

class NetworkDeviceFactory
{
    public static function make(Router $router, ?Plan $plan = null): NetworkDeviceAdapter
    {
        $device = $plan?->device ?? $router->type ?? '';

        return match ($device) {
            'MikrotikHotspot' => new MikrotikHotspotDriver($router),
            'MikrotikPppoe' => new MikrotikPppoeDriver($router),
            'Radius' => new RadiusDriver($router),
            default => self::resolveFromType($router, $device),
        };
    }

    private static function resolveFromType(Router $router, string $device): NetworkDeviceAdapter
    {
        return match (true) {
            str_contains($router->type, 'Hotspot') => new MikrotikHotspotDriver($router),
            str_contains($router->type, 'PPPoE') || str_contains($router->type, 'Pppoe') => new MikrotikPppoeDriver($router),
            str_contains($router->type, 'Radius') => new RadiusDriver($router),
            default => new DummyDriver($router),
        };
    }
}
