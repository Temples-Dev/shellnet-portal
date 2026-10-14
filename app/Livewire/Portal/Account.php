<?php

namespace App\Livewire\Portal;

use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Account extends Component
{
    #[Computed]
    public function customer(): Customer
    {
        return auth('customer')->user();
    }

    #[Computed]
    public function activeSubscription(): ?Subscription
    {
        return Subscription::where('customer_id', $this->customer->id)
            ->where('status', 'on')
            ->latest()
            ->first();
    }

    #[Computed]
    public function paymentTransactions(): Collection
    {
        return PaymentTransaction::where('customer_id', $this->customer->id)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.portal.account');
    }
}
