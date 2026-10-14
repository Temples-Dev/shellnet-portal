<?php

namespace App\Livewire\Services;

use App\Models\Bandwidth as BandwidthModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Bandwidth extends Component
{
    use WithPagination;

    public string $search = '';

    public string $fName = '';

    public int $fRateDown = 10;

    public string $fRateDownUnit = 'Mbps';

    public int $fRateUp = 5;

    public string $fRateUpUnit = 'Mbps';

    public string $fBurst = '';

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $deletingName = '';

    private const UNITS = ['Kbps', 'Mbps', 'Gbps'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function bandwidths()
    {
        return BandwidthModel::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);
    }

    public function units(): array
    {
        return self::UNITS;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetErrorBag();
        $this->modal('bw-form')->show();
    }

    public function openEdit(int $id): void
    {
        $bw = BandwidthModel::findOrFail($id);
        $this->editingId = $id;
        $this->fName = $bw->name;
        $this->fRateDown = $bw->rate_down;
        $this->fRateDownUnit = $bw->rate_down_unit;
        $this->fRateUp = $bw->rate_up;
        $this->fRateUpUnit = $bw->rate_up_unit;
        $this->fBurst = $bw->burst ?? '';
        $this->resetErrorBag();
        $this->modal('bw-form')->show();
    }

    public function save(): void
    {
        $this->validate([
            'fName' => 'required|string|max:64',
            'fRateDown' => 'required|integer|min:1',
            'fRateDownUnit' => 'required|in:'.implode(',', self::UNITS),
            'fRateUp' => 'required|integer|min:1',
            'fRateUpUnit' => 'required|in:'.implode(',', self::UNITS),
        ]);

        $data = [
            'name' => $this->fName,
            'rate_down' => $this->fRateDown,
            'rate_down_unit' => $this->fRateDownUnit,
            'rate_up' => $this->fRateUp,
            'rate_up_unit' => $this->fRateUpUnit,
            'burst' => $this->fBurst ?: null,
        ];

        $this->editingId
            ? BandwidthModel::findOrFail($this->editingId)->update($data)
            : BandwidthModel::create($data);

        $this->modal('bw-form')->close();
        $this->resetForm();
        $this->editingId = null;
    }

    public function confirmDelete(int $id): void
    {
        $bw = BandwidthModel::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $bw->name;
        $this->modal('bw-delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            BandwidthModel::findOrFail($this->deletingId)->delete();
        }
        $this->deletingId = null;
        $this->modal('bw-delete')->close();
    }

    private function resetForm(): void
    {
        $this->fName = '';
        $this->fRateDown = 10;
        $this->fRateDownUnit = 'Mbps';
        $this->fRateUp = 5;
        $this->fRateUpUnit = 'Mbps';
        $this->fBurst = '';
    }

    public function render(): View
    {
        return view('livewire.services.bandwidth');
    }
}
