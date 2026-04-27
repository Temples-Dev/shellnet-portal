<?php

namespace App\Livewire\Billing;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Voucher;
use App\Services\RouterOS\RouterOSClientFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use RouterOS\Query;

class Vouchers extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = '';
    public string $planFilter   = '';

    // Generate form
    public int    $genCount    = 10;
    public string $genPlanId   = '';
    public string $genType     = 'Hotspot';
    public string $genRouterId = '';

    #[Computed]
    public function vouchers()
    {
        return Voucher::with(['plan', 'customer'])
            ->when($this->search, fn ($q) => $q->where('code', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->planFilter, fn ($q) => $q->where('plan_id', $this->planFilter))
            ->latest()
            ->paginate(25);
    }

    #[Computed]
    public function plans()
    {
        return Plan::orderBy('name')->get(['id', 'name', 'type']);
    }

    #[Computed]
    public function routers()
    {
        return Router::orderBy('name')->get(['id', 'name']);
    }

    public function openGenerate(): void
    {
        $this->reset(['genCount', 'genPlanId', 'genType', 'genRouterId']);
        $this->genCount = 10;
        $this->genType  = 'Hotspot';
        $this->modal('voucher-generate')->show();
    }

    public function generate(): void
    {
        $this->validate([
            'genCount'    => 'required|integer|min:1|max:500',
            'genPlanId'   => 'required|exists:plans,id',
            'genType'     => 'required|in:Hotspot',
            'genRouterId' => 'nullable|exists:routers,id',
        ]);

        $plan     = Plan::with('router')->find($this->genPlanId);
        $routerId = $this->genRouterId ?: $plan?->router_id;
        $router   = $routerId ? Router::find($routerId) : null;

        $now   = now();
        $codes = [];

        for ($i = 0; $i < $this->genCount; $i++) {
            $codes[] = [
                'code'         => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                'type'         => $this->genType,
                'plan_id'      => $this->genPlanId ?: null,
                'router_id'    => $routerId ?: null,
                'status'       => 'unused',
                'generated_by' => auth()->id(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        Voucher::insert($codes);

        // Push voucher codes to MikroTik as hotspot users (non-fatal)
        if ($router && $this->genType === 'Hotspot') {
            $this->pushHotspotVouchers($router, $plan, $codes);
        }

        $this->modal('voucher-generate')->close();
        unset($this->vouchers);
    }

    private function pushHotspotVouchers(Router $router, Plan $plan, array $codes): void
    {
        try {
            $client = RouterOSClientFactory::make($router);

            foreach ($codes as $row) {
                $code = $row['code'];

                // Skip if already exists on router
                $existing = $client->query('/ip/hotspot/user/print', [['name', '=', $code]])->read();
                if (! empty($existing)) {
                    continue;
                }

                $query = (new Query('/ip/hotspot/user/add'))
                    ->equal('name', $code)
                    ->equal('password', $code)
                    ->equal('profile', $plan->name)
                    ->equal('comment', 'voucher');

                $client->query($query)->read();
            }
        } catch (\Throwable $e) {
            Log::warning('[Vouchers] Router push failed', [
                'router' => $router->name,
                'error'  => $e->getMessage(),
            ]);
            session()->flash('routerWarning', "Vouchers saved but could not be pushed to router \"{$router->name}\": " . $e->getMessage());
        }
    }

    public function disable(int $id): void
    {
        Voucher::where('id', $id)->where('status', 'unused')->update(['status' => 'disabled']);
        unset($this->vouchers);
    }

    public function delete(int $id): void
    {
        Voucher::where('id', $id)->where('status', 'unused')->delete();
        unset($this->vouchers);
    }

    public function exportCsv(): mixed
    {
        $vouchers = Voucher::with('plan')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->planFilter, fn ($q) => $q->where('plan_id', $this->planFilter))
            ->get();

        $rows = [['Code', 'Type', 'Plan', 'Status', 'Used At', 'Created At']];

        foreach ($vouchers as $v) {
            $rows[] = [
                $v->code,
                $v->type,
                $v->plan?->name ?? '',
                $v->status,
                $v->used_at?->format('d M Y H:i') ?? '',
                $v->created_at->format('d M Y'),
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            $fp = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
        }, 'vouchers-' . date('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedPlanFilter(): void { $this->resetPage(); }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.billing.vouchers');
    }
}
