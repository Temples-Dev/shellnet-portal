<?php

namespace App\Livewire;

use App\Models\Router;
use App\Services\RouterOS\RouterOSClientFactory;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LiveSessions extends Component
{
    public bool $loading = false;

    #[Computed]
    public function sessions(): array
    {
        $all     = [];
        $routers = Router::enabled()->where('status', 'Online')->get();

        foreach ($routers as $router) {
            try {
                $client   = RouterOSClientFactory::make($router, timeout: 5);
                $raw      = $client->query('/ip/hotspot/active/print')->read();

                foreach ($raw as $entry) {
                    $all[] = [
                        'router'   => $router->name,
                        'user'     => $entry['user']        ?? '—',
                        'address'  => $entry['address']     ?? '—',
                        'mac'      => $entry['mac-address'] ?? '—',
                        'uptime'   => $entry['uptime']      ?? '—',
                        'bytes_in' => $this->formatBytes((int) ($entry['bytes-in']  ?? 0)),
                        'bytes_out'=> $this->formatBytes((int) ($entry['bytes-out'] ?? 0)),
                    ];
                }
            } catch (\Throwable $e) {
                Log::debug('[LiveSessions] Could not query router', [
                    'router' => $router->name,
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        return $all;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.live-sessions');
    }
}
