<?php

namespace App\Livewire\Network;

use App\Models\Nas;
use App\Models\Router;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Radius extends Component
{
    use WithPagination;

    public string $search = '';

    // Form
    public ?int   $editingId    = null;
    public string $fNasname     = '';
    public string $fShortname   = '';
    public string $fType        = 'other';
    public string $fPorts       = '';
    public string $fSecret      = '';
    public string $fServer      = '';
    public string $fCommunity   = '';
    public string $fDescription = '';
    public string $fRouterId    = '';

    // Delete
    public ?int   $deletingId   = null;
    public string $deletingName = '';

    public const NAS_TYPES = ['other', 'cisco', 'livingston', 'max40xx', 'multitech', 'portslave', 'tc', 'usrhiper', 'mikrotik'];

    #[Computed]
    public function nasDevices()
    {
        return Nas::with('router')
            ->when($this->search, fn ($q) => $q
                ->where('nasname', 'like', "%{$this->search}%")
                ->orWhere('shortname', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function routers()
    {
        return Router::orderBy('name')->get(['id', 'name']);
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'fNasname', 'fShortname', 'fType', 'fPorts',
            'fSecret', 'fServer', 'fCommunity', 'fDescription', 'fRouterId']);
        $this->fType = 'other';
        $this->modal('nas-form')->show();
    }

    public function openEdit(int $id): void
    {
        $n = Nas::findOrFail($id);
        $this->editingId    = $id;
        $this->fNasname     = $n->nasname;
        $this->fShortname   = $n->shortname ?? '';
        $this->fType        = $n->type;
        $this->fPorts       = $n->ports !== null ? (string) $n->ports : '';
        $this->fSecret      = '';
        $this->fServer      = $n->server ?? '';
        $this->fCommunity   = $n->community ?? '';
        $this->fDescription = $n->description ?? '';
        $this->fRouterId    = $n->router_id !== null ? (string) $n->router_id : '';
        $this->modal('nas-form')->show();
    }

    public function save(): void
    {
        $secretRule = $this->editingId ? 'nullable|string|max:60' : 'required|string|max:60';

        $this->validate([
            'fNasname'     => 'required|string|max:128',
            'fShortname'   => 'nullable|string|max:32',
            'fType'        => 'required|string|max:30',
            'fPorts'       => 'nullable|integer|min:0|max:65535',
            'fSecret'      => $secretRule,
            'fServer'      => 'nullable|string|max:64',
            'fCommunity'   => 'nullable|string|max:50',
            'fDescription' => 'nullable|string|max:200',
            'fRouterId'    => 'nullable|exists:routers,id',
        ]);

        $data = [
            'nasname'     => $this->fNasname,
            'shortname'   => $this->fShortname ?: null,
            'type'        => $this->fType,
            'ports'       => $this->fPorts !== '' ? (int) $this->fPorts : null,
            'server'      => $this->fServer ?: null,
            'community'   => $this->fCommunity ?: null,
            'description' => $this->fDescription ?: null,
            'router_id'   => $this->fRouterId !== '' ? (int) $this->fRouterId : null,
        ];

        if ($this->fSecret !== '') {
            $data['secret'] = $this->fSecret;
        }

        if ($this->editingId) {
            Nas::findOrFail($this->editingId)->update($data);
        } else {
            Nas::create($data);
        }

        $this->modal('nas-form')->close();
        unset($this->nasDevices);
    }

    public function confirmDelete(int $id): void
    {
        $n = Nas::findOrFail($id);
        $this->deletingId   = $id;
        $this->deletingName = $n->nasname;
        $this->modal('nas-delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Nas::destroy($this->deletingId);
            $this->deletingId = null;
        }
        $this->modal('nas-delete')->close();
        unset($this->nasDevices);
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.network.radius');
    }
}
