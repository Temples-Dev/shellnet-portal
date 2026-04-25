<?php

namespace App\Services\RouterOS;

use App\Models\Router;
use App\Services\RouterOS\Contracts\DeviceDriverInterface;
use App\Services\RouterOS\Drivers\DummyDriver;
use App\Services\RouterOS\Drivers\MikrotikHotspotDriver;
use App\Services\RouterOS\Drivers\MikrotikPPPoEDriver;
use Illuminate\Support\Facades\Log;

class RouterOSManager
{
    /**
     * Resolve the correct device driver for a given router and plan type.
     *
     * Falls back to DummyDriver if the router is null, disabled, or unreachable.
     */
    public function driver(?Router $router, string $planType): DeviceDriverInterface
    {
        if (!$router || !$router->enabled) {
            return new DummyDriver();
        }

        try {
            $client = RouterOSClientFactory::make($router);
        } catch (\Throwable $e) {
            Log::warning('[RouterOSManager] Could not connect to router', [
                'router' => $router->name,
                'error' => $e->getMessage(),
            ]);
            return new DummyDriver();
        }

        return match ($planType) {
            'Hotspot' => new MikrotikHotspotDriver($client),
            'PPPoE' => new MikrotikPPPoEDriver($client),
            default => new DummyDriver(),
        };
    }
}
