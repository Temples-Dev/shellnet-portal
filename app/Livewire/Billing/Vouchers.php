<?php

namespace App\Livewire\Billing;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Voucher;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

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
            'genType'     => 'required|in:Hotspot,PPPoE,VPN',
            'genRouterId' => 'nullable|exists:routers,id',
        ]);

        $now   = now();
        $codes = [];

        for ($i = 0; $i < $this->genCount; $i++) {
            $codes[] = [
                'code'         => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                'type'         => $this->genType,
                'plan_id'      => $this->genPlanId ?: null,
                'router_id'    => $this->genRouterId ?: null,
                'status'       => 'unused',
                'generated_by' => auth()->id(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        Voucher::insert($codes);

        $this->modal('voucher-generate')->close();
        unset($this->vouchers);
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
