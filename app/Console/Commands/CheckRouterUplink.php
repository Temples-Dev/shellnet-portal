<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\RouterOS\RouterOSClientFactory;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RouterOS\Query;

#[Signature('routers:check-uplink')]
#[Description('Check each enabled router via RouterOS API and verify Starlink/WAN uplink is active.')]
class CheckRouterUplink extends Command
{
    public function handle(): int
    {
        $routers = Router::enabled()->get();

        if ($routers->isEmpty()) {
            $this->info('No enabled routers.');

            return self::SUCCESS;
        }

        foreach ($routers as $router) {
            [$status, $reason] = $this->probe($router);

            $router->update([
                'status' => $status,
                'last_seen' => $status === 'Online' ? now() : $router->last_seen,
            ]);

            $icon = $status === 'Online' ? '✓' : '✗';
            $method = $status === 'Online' ? 'info' : 'warn';
            $this->$method("{$icon} [{$router->name}] {$status} — {$reason}");
        }

        return self::SUCCESS;
    }

    private function probe(Router $router): array
    {
        try {
            $client = RouterOSClientFactory::make($router, timeout: 8);
        } catch (\Throwable $e) {
            Log::info('[CheckRouterUplink] Cannot reach router', [
                'router' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return ['Offline', 'API unreachable: '.$e->getMessage()];
        }

        // Router is reachable. Now verify internet uplink via active default route.
        try {
            $routes = $client->query('/ip/route/print')->read();

            $hasActiveDefault = collect($routes)->contains(function (array $r): bool {
                $dst = $r['dst-address'] ?? '';
                $active = $r['active'] ?? 'false';

                return $dst === '0.0.0.0/0' && $active === 'true';
            });

            if ($hasActiveDefault) {
                return ['Online', 'API ok, default route active'];
            }

            return ['Offline', 'API ok but no active default route (Starlink down?)'];
        } catch (\Throwable $e) {
            // API connection worked but route query failed — still mark online
            Log::debug('[CheckRouterUplink] Route query failed', [
                'router' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return ['Online', 'API ok (route check skipped)'];
        }
    }
}
