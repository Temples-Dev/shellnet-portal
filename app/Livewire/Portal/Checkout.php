<?php

namespace App\Livewire\Portal;

use App\Models\Customer;
use App\Models\Plan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Checkout extends Component
{
    public Plan $plan;

    public string $fullname = '';
    public string $phone    = '';
    public string $email    = '';

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;

        if ($customer = auth('customer')->user()) {
            $this->fullname = $customer->fullname;
            $this->phone    = $customer->phone ?? '';
            $this->email    = $customer->email ?? '';
        }
    }

    #[Computed]
    public function isReturning(): bool
    {
        return (bool) auth('customer')->check();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.portal.checkout');
    }
}
