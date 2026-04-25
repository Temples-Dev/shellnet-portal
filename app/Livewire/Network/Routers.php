<?php

namespace App\Livewire\Network;

use App\Models\Router;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Routers extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    // Shared form fields
    public string $fName = '';
    public string $fIpAddress = '';
    public string $fUsername = 'admin';
    public string $fPassword = '';
    public string $fDescription = '';
    public bool   $fEnabled = true;

    public ?int $editingId = null;
    public ?int $deletingId = null;
    public string $deletingName = '';

    public array $pingResults = [];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function routers()
    {
        return Router::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('ip_address', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate(15);
    }

    // -------------------------------------------------------------------------
    // Create / Edit
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetErrorBag();
        $this->modal('router-form')->show();
    }

    public function openEdit(int $id): void
    {
        $r = Router::findOrFail($id);
        $this->editingId    = $id;
        $this->fName        = $r->name;
        $this->fIpAddress   = $r->ip_address;
        $this->fUsername    = $r->username;
        $this->fPassword    = '';
        $this->fDescription = $r->description ?? '';
        $this->fEnabled     = (bool) $r->enabled;
        $this->resetErrorBag();
        $this->modal('router-form')->show();
    }

    public function save(): void
    {
        $rules = [
            'fName'      => 'required|string|max:64|unique:routers,name' . ($this->editingId ? ",{$this->editingId}" : ''),
            'fIpAddress' => 'required|string|max:64',
            'fUsername'  => 'required|string|max:64',
        ];

        if (! $this->editingId) {
            $rules['fPassword'] = 'required|string|min:1';
        }

        $this->validate($rules);

        $data = [
            'name'        => $this->fName,
            'ip_address'  => $this->fIpAddress,
            'username'    => $this->fUsername,
            'description' => $this->fDescription ?: null,
            'enabled'     => $this->fEnabled,
        ];

        if ($this->fPassword !== '') {
            $data['password'] = $this->fPassword;
        }

        if ($this->editingId) {
            Router::findOrFail($this->editingId)->update($data);
        } else {
            Router::create(array_merge($data, ['status' => 'Offline']));
        }

        $this->modal('router-form')->close();
        $this->resetForm();
        $this->editingId = null;
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function confirmDelete(int $id): void
    {
        $r = Router::findOrFail($id);
        $this->deletingId   = $id;
        $this->deletingName = $r->name;
        $this->modal('delete-router')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Router::findOrFail($this->deletingId)->delete();
        }
        $this->deletingId   = null;
        $this->deletingName = '';
        $this->modal('delete-router')->close();
    }

    // -------------------------------------------------------------------------
    // Toggle enabled
    // -------------------------------------------------------------------------

    public function toggleEnabled(int $id): void
    {
        $router = Router::findOrFail($id);
        $router->update(['enabled' => ! $router->enabled]);
    }

    // -------------------------------------------------------------------------
    // Ping — uses proc_open with argv array (no shell expansion)
    // -------------------------------------------------------------------------

    public function ping(int $id): void
    {
        $router = Router::findOrFail($id);
        $ip     = $router->clean_ip;

        $alive = $this->icmpPing($ip);

        $status = $alive ? 'Online' : 'Offline';
        $router->update([
            'status'    => $status,
            'last_seen' => $alive ? now() : $router->last_seen,
        ]);

        $this->pingResults[$id] = $status;
    }

    private function icmpPing(string $ip): bool
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // argv array — no shell involved, immune to injection
        $cmd     = ['ping', '-c', '1', '-W', '2', $ip];
        $process = proc_open($cmd, $descriptors, $pipes);

        if (! is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return $exitCode === 0
            || str_contains((string) $stdout, '1 received')
            || str_contains((string) $stdout, '1 packets received');
    }

    private function resetForm(): void
    {
        $this->fName        = '';
        $this->fIpAddress   = '';
        $this->fUsername    = 'admin';
        $this->fPassword    = '';
        $this->fDescription = '';
        $this->fEnabled     = true;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.network.routers');
    }
}
