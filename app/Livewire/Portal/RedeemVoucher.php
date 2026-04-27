<?php

namespace App\Livewire\Portal;

use App\Actions\Subscriptions\AssignSubscription;
use App\Models\Customer;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RedeemVoucher extends Component
{
    public string $code = '';

    public function redeem(AssignSubscription $assignSubscription): mixed
    {
        $this->validate(['code' => 'required|string|max:50']);

        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $voucher = Voucher::with('plan')
            ->where('code', strtoupper(trim($this->code)))
            ->where('status', 'unused')
            ->first();

        if (! $voucher) {
            $this->addError('code', 'Invalid or already used voucher code.');
            return null;
        }

        $plan = $voucher->plan;

        if (! $plan || ! $plan->enabled) {
            $this->addError('code', 'This voucher is no longer valid.');
            return null;
        }

        // If voucher is locked to a specific customer, enforce it
        if ($voucher->customer_id && $voucher->customer_id !== $customer->id) {
            $this->addError('code', 'This voucher is not assigned to your account.');
            return null;
        }

        try {
            $assignSubscription->execute($customer, $plan, 'voucher');

            $voucher->update([
                'status'      => 'used',
                'customer_id' => $customer->id,
                'used_at'     => now(),
            ]);

            return redirect()->route('portal.success');
        } catch (\Throwable $e) {
            Log::error('[RedeemVoucher] Failed', ['voucher' => $voucher->id, 'error' => $e->getMessage()]);
            $this->addError('code', 'Could not activate voucher. Please try again.');
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.portal.redeem-voucher');
    }
}
