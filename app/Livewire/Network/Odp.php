<?php

namespace App\Livewire\Network;

use App\Models\Odp as OdpModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Odp extends Component
{
    use WithPagination;

    public string $search = '';

    // Form
    public ?int $editingId = null;

    public string $fName = '';

    public string $fPortAmount = '0';

    public string $fAttenuation = '';

    public string $fAddress = '';

    public string $fCoordinates = '';

    public string $fCoverage = '';

    // Delete
    public ?int $deletingId = null;

    public string $deletingName = '';

    #[Computed]
    public function odps()
    {
        return OdpModel::when($this->search, fn ($q) => $q
            ->where('name', 'like', "%{$this->search}%")
            ->orWhere('address', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(20);
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'fName', 'fPortAmount', 'fAttenuation', 'fAddress', 'fCoordinates', 'fCoverage']);
        $this->fPortAmount = '0';
        $this->modal('odp-form')->show();
    }

    public function openEdit(int $id): void
    {
        $o = OdpModel::findOrFail($id);
        $this->editingId = $id;
        $this->fName = $o->name;
        $this->fPortAmount = (string) $o->port_amount;
        $this->fAttenuation = $o->attenuation !== null ? (string) $o->attenuation : '';
        $this->fAddress = $o->address ?? '';
        $this->fCoordinates = $o->coordinates ?? '';
        $this->fCoverage = $o->coverage !== null ? (string) $o->coverage : '';
        $this->modal('odp-form')->show();
    }

    public function save(): void
    {
        $this->validate([
            'fName' => 'required|string|max:32',
            'fPortAmount' => 'required|integer|min:0|max:65535',
            'fAttenuation' => 'nullable|numeric|min:0',
            'fAddress' => 'nullable|string|max:500',
            'fCoordinates' => ['nullable', 'string', 'max:50', 'regex:/^-?\d+(\.\d+)?[,\s]-?\d+(\.\d+)?$/'],
            'fCoverage' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $this->fName,
            'port_amount' => (int) $this->fPortAmount,
            'attenuation' => $this->fAttenuation !== '' ? (float) $this->fAttenuation : null,
            'address' => $this->fAddress ?: null,
            'coordinates' => $this->fCoordinates ?: null,
            'coverage' => $this->fCoverage !== '' ? (int) $this->fCoverage : null,
        ];

        if ($this->editingId) {
            OdpModel::findOrFail($this->editingId)->update($data);
        } else {
            OdpModel::create($data);
        }

        $this->modal('odp-form')->close();
        unset($this->odps);
    }

    public function confirmDelete(int $id): void
    {
        $o = OdpModel::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $o->name;
        $this->modal('odp-delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            OdpModel::destroy($this->deletingId);
            $this->deletingId = null;
        }
        $this->modal('odp-delete')->close();
        unset($this->odps);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.network.odp');
    }
}
