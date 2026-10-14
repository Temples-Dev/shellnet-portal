<?php

namespace App\Livewire\Reports;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Logs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function logs()
    {
        return ActivityLog::query()
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(25);
    }

    #[Computed]
    public function types(): array
    {
        return ActivityLog::distinct()->orderBy('type')->pluck('type')->toArray();
    }

    public function render(): View
    {
        return view('livewire.reports.logs');
    }
}
