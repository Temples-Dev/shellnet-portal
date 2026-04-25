<?php

namespace App\Livewire\Billing;

use App\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $methodFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }
    public function updatedMethodFilter(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function sort(string $col): void
    {
        if ($this->sortBy === $col) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $col;
            $this->sortDirection = 'desc';
        }
    }

    #[Computed]
    public function transactions()
    {
        return Transaction::with('customer')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('invoice', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%")
                  ->orWhere('plan_name', 'like', "%{$this->search}%");
            }))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->methodFilter, fn ($q) => $q->where('method', $this->methodFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    #[Computed]
    public function totals(): array
    {
        $query = Transaction::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('invoice', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%");
            }))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->methodFilter, fn ($q) => $q->where('method', $this->methodFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        return [
            'count'   => $query->count(),
            'revenue' => (float) $query->sum('price'),
        ];
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = Transaction::with('customer')
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->methodFilter, fn ($q) => $q->where('method', $this->methodFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'transactions-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice', 'Customer', 'Plan', 'Type', 'Method', 'Amount', 'Date']);
            foreach ($rows as $tx) {
                fputcsv($handle, [
                    $tx->invoice,
                    $tx->customer?->fullname ?? $tx->username,
                    $tx->plan_name,
                    $tx->type,
                    $tx->method,
                    number_format($tx->price, 2),
                    $tx->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.billing.transactions');
    }
}
