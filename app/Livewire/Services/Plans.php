<?php

namespace App\Livewire\Services;

use App\Models\Bandwidth;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Plans extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    // Shared form fields
    public string $fName = '';

    public string $fType = 'Hotspot';

    public string $fPrice = '';

    public ?int $fBandwidthId = null;

    public ?int $fRouterId = null;

    public int $fValidity = 30;

    public string $fValidityUnit = 'Days';

    public bool $fEnabled = true;

    public bool $fPrepaid = true;

    public int $fSharedUsers = 1;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $col): void
    {
        $this->sortBy = $this->sortBy === $col && $this->sortDirection === 'asc'
            ? $this->sortBy
            : $col;
        $this->sortDirection = $this->sortBy === $col && $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    #[Computed]
    public function plans()
    {
        return Plan::query()
            ->with(['bandwidth', 'router'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->typeFilter, fn ($q) => $q->ofType($this->typeFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    #[Computed]
    public function bandwidths()
    {
        return Bandwidth::orderBy('name')->get();
    }

    #[Computed]
    public function routers()
    {
        return Router::enabled()->orderBy('name')->get();
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetErrorBag();
        $this->modal('plan-form')->show();
    }

    public function openEdit(int $id): void
    {
        $p = Plan::findOrFail($id);
        $this->editingId = $id;
        $this->fName = $p->name;
        $this->fType = $p->type;
        $this->fPrice = (string) $p->price;
        $this->fBandwidthId = $p->bandwidth_id;
        $this->fRouterId = $p->router_id;
        $this->fValidity = $p->validity;
        $this->fValidityUnit = $p->validity_unit;
        $this->fEnabled = (bool) $p->enabled;
        $this->fPrepaid = (bool) $p->prepaid;
        $this->fSharedUsers = (int) $p->shared_users;
        $this->resetErrorBag();
        $this->modal('plan-form')->show();
    }

    public function save(): void
    {
        $this->validate([
            'fName' => 'required|string|max:64',
            'fType' => 'required|in:'.implode(',', Plan::TYPES),
            'fPrice' => 'required|numeric|min:0',
            'fBandwidthId' => 'nullable|exists:bandwidths,id',
            'fRouterId' => 'nullable|exists:routers,id',
            'fValidity' => 'required|integer|min:1',
            'fValidityUnit' => 'required|in:'.implode(',', Plan::VALIDITY_UNITS),
            'fSharedUsers' => 'required|integer|min:1|max:10',
        ]);

        $data = [
            'name' => $this->fName,
            'type' => $this->fType,
            'price' => (float) $this->fPrice,
            'bandwidth_id' => $this->fBandwidthId,
            'router_id' => $this->fRouterId,
            'validity' => $this->fValidity,
            'validity_unit' => $this->fValidityUnit,
            'enabled' => $this->fEnabled,
            'prepaid' => $this->fPrepaid,
            'shared_users' => $this->fSharedUsers,
        ];

        if ($this->editingId) {
            Plan::findOrFail($this->editingId)->update($data);
        } else {
            Plan::create(array_merge($data, [
                'is_limited' => false,
                'limit_type' => 'Time_Limit',
                'is_radius' => false,
                'plan_type' => 'Personal',
                'device' => 'mikrotik',
            ]));
        }

        $this->modal('plan-form')->close();
        $this->resetForm();
        $this->editingId = null;
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function confirmDelete(int $id): void
    {
        $p = Plan::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $p->name;
        $this->modal('delete-plan')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Plan::findOrFail($this->deletingId)->delete();
        }
        $this->deletingId = null;
        $this->deletingName = '';
        $this->modal('delete-plan')->close();
    }

    // -------------------------------------------------------------------------
    // Toggle enabled
    // -------------------------------------------------------------------------

    public function toggleEnabled(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $plan->update(['enabled' => ! $plan->enabled]);
    }

    private function resetForm(): void
    {
        $this->fName = '';
        $this->fType = 'Hotspot';
        $this->fPrice = '';
        $this->fBandwidthId = null;
        $this->fRouterId = null;
        $this->fValidity = 30;
        $this->fValidityUnit = 'Days';
        $this->fEnabled = true;
        $this->fPrepaid = true;
        $this->fSharedUsers = 1;
    }

    public function render(): View
    {
        return view('livewire.services.plans');
    }
}
