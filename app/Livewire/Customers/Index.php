<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $serviceFilter = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    // Create form fields
    public string $createUsername = '';

    public string $createFullname = '';

    public string $createEmail = '';

    public string $createPhone = '';

    public string $createPassword = '';

    public string $createMacAddress = '';

    public string $createServiceType = 'Hotspot';

    public string $createStatus = 'Active';

    // Edit form fields
    public ?int $editingId = null;

    public string $editUsername = '';

    public string $editFullname = '';

    public string $editEmail = '';

    public string $editPhone = '';

    public string $editMacAddress = '';

    public string $editServiceType = '';

    public string $editStatus = '';

    // Delete confirm
    public ?int $deletingId = null;

    public string $deletingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedServiceFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->serviceFilter, fn ($q) => $q->where('service_type', $this->serviceFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->resetCreateForm();
        $this->resetErrorBag();
        $this->modal('create-customer')->show();
    }

    public function saveCreate(): void
    {
        $this->validate([
            'createUsername' => 'required|string|max:64|unique:customers,username',
            'createFullname' => 'required|string|max:128',
            'createEmail' => 'nullable|email|max:128|unique:customers,email',
            'createPhone' => 'nullable|string|max:32',
            'createPassword' => 'required|string|min:6',
            'createMacAddress' => 'nullable|regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/',
            'createServiceType' => 'required|in:Hotspot',
            'createStatus' => 'required|in:'.implode(',', Customer::STATUSES),
        ]);

        Customer::create([
            'username' => $this->createUsername,
            'fullname' => $this->createFullname,
            'email' => $this->createEmail ?: null,
            'phone' => $this->createPhone ?: null,
            'password' => bcrypt($this->createPassword),
            'mac_address' => $this->createMacAddress ? strtoupper($this->createMacAddress) : null,
            'service_type' => $this->createServiceType,
            'status' => $this->createStatus,
        ]);

        $this->modal('create-customer')->close();
        $this->resetCreateForm();
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function openEdit(int $id): void
    {
        $c = Customer::findOrFail($id);
        $this->editingId = $id;
        $this->editUsername = $c->username;
        $this->editFullname = $c->fullname;
        $this->editEmail = $c->email ?? '';
        $this->editPhone = $c->phone ?? '';
        $this->editMacAddress = $c->mac_address ?? '';
        $this->editServiceType = $c->service_type;
        $this->editStatus = $c->status;
        $this->resetErrorBag();
        $this->modal('edit-customer')->show();
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editUsername' => 'required|string|max:64|unique:customers,username,'.$this->editingId,
            'editFullname' => 'required|string|max:128',
            'editEmail' => 'nullable|email|max:128|unique:customers,email,'.$this->editingId,
            'editPhone' => 'nullable|string|max:32',
            'editMacAddress' => 'nullable|regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/',
            'editServiceType' => 'required|in:Hotspot',
            'editStatus' => 'required|in:'.implode(',', Customer::STATUSES),
        ]);

        Customer::findOrFail($this->editingId)->update([
            'username' => $this->editUsername,
            'fullname' => $this->editFullname,
            'email' => $this->editEmail ?: null,
            'phone' => $this->editPhone ?: null,
            'mac_address' => $this->editMacAddress ? strtoupper($this->editMacAddress) : null,
            'service_type' => $this->editServiceType,
            'status' => $this->editStatus,
        ]);

        $this->modal('edit-customer')->close();
        $this->editingId = null;
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function confirmDelete(int $id): void
    {
        $c = Customer::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $c->fullname;
        $this->modal('delete-customer')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Customer::findOrFail($this->deletingId)->delete();
        }
        $this->deletingId = null;
        $this->deletingName = '';
        $this->modal('delete-customer')->close();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resetCreateForm(): void
    {
        $this->createUsername = '';
        $this->createFullname = '';
        $this->createEmail = '';
        $this->createPhone = '';
        $this->createPassword = '';
        $this->createMacAddress = '';
        $this->createServiceType = 'Hotspot';
        $this->createStatus = 'Active';
    }

    public function render(): View
    {
        return view('livewire.customers.index');
    }
}
