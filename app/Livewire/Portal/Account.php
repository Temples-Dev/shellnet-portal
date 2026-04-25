<?php

namespace App\Livewire\Portal;

use App\Models\Subscription;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Account extends Component
{
    #[Computed]
    public function customer(): \App\Models\Customer
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
    public function recentSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('customer_id', $this->customer->id)
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.portal.account');
    }
}
