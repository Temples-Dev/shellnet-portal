<?php

namespace App\Livewire\Network;

use App\Models\IpPool;
use App\Models\Router;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Pools extends Component
{
    use WithPagination;

    public string $search = '';

    public string $routerFilter = '';

    public string $fName = '';

    public string $fLocalIp = '';

    public string $fRangeIp = '';

    public ?int $fRouterId = null;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRouterFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function pools()
    {
        return IpPool::with('router')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('range_ip', 'like', "%{$this->search}%"))
            ->when($this->routerFilter, fn ($q) => $q->where('router_id', $this->routerFilter))
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function routers()
    {
        return Router::enabled()->orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetErrorBag();
        $this->modal('pool-form')->show();
    }

    public function openEdit(int $id): void
    {
        $pool = IpPool::findOrFail($id);
        $this->editingId = $id;
        $this->fName = $pool->name;
        $this->fLocalIp = $pool->local_ip;
        $this->fRangeIp = $pool->range_ip;
        $this->fRouterId = $pool->router_id;
        $this->resetErrorBag();
        $this->modal('pool-form')->show();
    }

    public function save(): void
    {
        $this->validate([
            'fName' => 'required|string|max:64',
            'fLocalIp' => 'required|string|max:64',
            'fRangeIp' => 'required|string|max:128',
            'fRouterId' => 'nullable|exists:routers,id',
        ]);

        $data = [
            'name' => $this->fName,
            'local_ip' => $this->fLocalIp,
            'range_ip' => $this->fRangeIp,
            'router_id' => $this->fRouterId,
        ];

        $this->editingId
            ? IpPool::findOrFail($this->editingId)->update($data)
            : IpPool::create($data);

        $this->modal('pool-form')->close();
        $this->resetForm();
        $this->editingId = null;
    }

    public function confirmDelete(int $id): void
    {
        $pool = IpPool::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $pool->name;
        $this->modal('pool-delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            IpPool::findOrFail($this->deletingId)->delete();
        }
        $this->deletingId = null;
        $this->modal('pool-delete')->close();
    }

    private function resetForm(): void
    {
        $this->fName = '';
        $this->fLocalIp = '';
        $this->fRangeIp = '';
        $this->fRouterId = null;
    }

    public function render(): View
    {
        return view('livewire.network.pools');
    }
}
