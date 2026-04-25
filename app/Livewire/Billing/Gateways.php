<?php

namespace App\Livewire\Billing;

use App\Models\Customer;
use App\Models\Plan;
use App\Services\Paystack\PaystackService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Gateways extends Component
{
    // Paystack settings
    public string $paystackPublicKey = '';
    public string $paystackSecretKey = '';
    public bool   $paystackEnabled = false;

    // Generate payment link
    public ?int $linkCustomerId = null;
    public ?int $linkPlanId = null;
    public string $generatedUrl = '';

    public function mount(): void
    {
        $this->paystackPublicKey = \App\Models\Setting::get('paystack_public_key', '');
        $this->paystackSecretKey = \App\Models\Setting::get('paystack_secret_key', '');
        $this->paystackEnabled   = (bool) \App\Models\Setting::get('paystack_enabled', '0');
    }

    #[Computed]
    public function customers()
    {
        return Customer::active()->orderBy('fullname')->get(['id', 'fullname', 'username']);
    }

    #[Computed]
    public function plans()
    {
        return Plan::enabled()->orderBy('name')->get(['id', 'name', 'price']);
    }

    public function savePaystack(): void
    {
        $this->validate([
            'paystackPublicKey' => 'nullable|string|max:128',
            'paystackSecretKey' => 'nullable|string|max:128',
        ]);

        \App\Models\Setting::set('paystack_public_key', $this->paystackPublicKey);
        \App\Models\Setting::set('paystack_secret_key', $this->paystackSecretKey);
        \App\Models\Setting::set('paystack_enabled', $this->paystackEnabled ? '1' : '0');

        // Refresh config so PaystackService picks up new keys without restart
        config(['services.paystack.secret_key' => $this->paystackSecretKey]);

        session()->flash('paystack_saved', true);
    }

    public function generateLink(): void
    {
        $this->validate([
            'linkCustomerId' => 'required|exists:customers,id',
            'linkPlanId'     => 'required|exists:plans,id',
        ]);

        $customer = Customer::findOrFail($this->linkCustomerId);
        $plan     = Plan::findOrFail($this->linkPlanId);

        try {
            $data = app(PaystackService::class)->initializeTransaction(
                $customer,
                $plan,
                route('payment.paystack.callback')
            );
            $this->generatedUrl = $data['authorization_url'];
        } catch (\Throwable $e) {
            $this->addError('linkPlanId', 'Paystack error: ' . $e->getMessage());
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.billing.gateways');
    }
}
