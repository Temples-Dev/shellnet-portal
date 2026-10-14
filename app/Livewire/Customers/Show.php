<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public int $customerId;

    // Edit form fields
    public string $editFullname = '';

    public string $editEmail = '';

    public string $editPhone = '';

    public string $editAddress = '';

    public string $editCity = '';

    public string $editServiceType = '';

    public string $editStatus = '';

    public string $editAccountType = '';

    public function mount(int $id): void
    {
        $this->customerId = $id;
        $this->fillEditForm();
    }

    #[Computed]
    public function customer(): Customer
    {
        return Customer::findOrFail($this->customerId);
    }

    #[Computed]
    public function activeSubscription()
    {
        return $this->customer->activeSubscription;
    }

    #[Computed]
    public function transactions()
    {
        return $this->customer->transactions()
            ->latest()
            ->limit(10)
            ->get();
    }

    public function openEdit(): void
    {
        $this->fillEditForm();
        $this->resetErrorBag();
        $this->modal('edit-customer')->show();
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editFullname' => 'required|string|max:128',
            'editEmail' => 'nullable|email|max:128|unique:customers,email,'.$this->customerId,
            'editPhone' => 'nullable|string|max:32',
            'editAddress' => 'nullable|string|max:255',
            'editCity' => 'nullable|string|max:64',
            'editServiceType' => 'required|in:'.implode(',', Customer::SERVICE_TYPES),
            'editStatus' => 'required|in:'.implode(',', Customer::STATUSES),
            'editAccountType' => 'required|in:'.implode(',', Customer::ACCOUNT_TYPES),
        ]);

        $this->customer->update([
            'fullname' => $this->editFullname,
            'email' => $this->editEmail ?: null,
            'phone' => $this->editPhone ?: null,
            'address' => $this->editAddress ?: null,
            'city' => $this->editCity ?: null,
            'service_type' => $this->editServiceType,
            'status' => $this->editStatus,
            'account_type' => $this->editAccountType,
        ]);

        unset($this->customer);
        $this->modal('edit-customer')->close();
    }

    private function fillEditForm(): void
    {
        $c = Customer::findOrFail($this->customerId);
        $this->editFullname = $c->fullname;
        $this->editEmail = $c->email ?? '';
        $this->editPhone = $c->phone ?? '';
        $this->editAddress = $c->address ?? '';
        $this->editCity = $c->city ?? '';
        $this->editServiceType = $c->service_type;
        $this->editStatus = $c->status;
        $this->editAccountType = $c->account_type ?? 'Personal';
    }

    public function render(): View
    {
        return view('livewire.customers.show');
    }
}
