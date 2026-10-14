<?php

namespace App\Livewire\Portal;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Checkout extends Component
{
    public Plan $plan;

    public string $coupon_code = '';

    public ?string $couponMessage = null;

    public bool $couponValid = false;

    public float $discountAmount = 0.0;

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;
    }

    public function applyCoupon(): void
    {
        $this->couponMessage = null;
        $this->couponValid = false;
        $this->discountAmount = 0.0;

        $code = strtoupper(trim($this->coupon_code));

        if (blank($code)) {
            $this->couponMessage = 'Enter a coupon code.';

            return;
        }

        $coupon = Coupon::active()->where('code', $code)->first();

        if (! $coupon || ! $coupon->is_valid) {
            $this->couponMessage = 'Invalid or expired coupon.';

            return;
        }

        if ($coupon->min_order_amount && $this->plan->price < $coupon->min_order_amount) {
            $this->couponMessage = 'Minimum order ₵'.number_format((float) $coupon->min_order_amount, 2).' required.';

            return;
        }

        $customer = auth('customer')->user();
        if ($coupon->customer_id && $coupon->customer_id !== $customer?->id) {
            $this->couponMessage = 'This coupon is not valid for your account.';

            return;
        }

        $this->discountAmount = $coupon->calculateDiscount((float) $this->plan->price);
        $this->couponValid = true;
        $this->couponMessage = 'Coupon applied — you save ₵'.number_format($this->discountAmount, 2).'!';
    }

    public function removeCoupon(): void
    {
        $this->coupon_code = '';
        $this->couponMessage = null;
        $this->couponValid = false;
        $this->discountAmount = 0.0;
    }

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function activeSubscription(): ?Subscription
    {
        $customer = auth('customer')->user();

        return $customer
            ? Subscription::where('customer_id', $customer->id)->where('status', 'on')->latest()->first()
            : null;
    }

    #[Computed]
    public function activePlan(): ?Plan
    {
        return $this->activeSubscription
            ? Plan::find($this->activeSubscription->plan_id)
            : null;
    }

    /**
     * Returns: 'new' | 'extend' | 'upgrade' | 'downgrade'
     *
     * Same plan          → extend (add validity on top)
     * Higher/equal price → upgrade (replace current)
     * Lower price        → downgrade (blocked)
     */
    #[Computed]
    public function subscriptionAction(): string
    {
        $active = $this->activeSubscription;
        if (! $active) {
            return 'new';
        }

        if ($active->plan_id === $this->plan->id) {
            return 'extend';
        }

        $activePlan = $this->activePlan;
        if (! $activePlan) {
            return 'new';
        }

        return (float) $this->plan->price >= (float) $activePlan->price ? 'upgrade' : 'downgrade';
    }

    /**
     * The projected new expiry date when extending the same plan.
     */
    #[Computed]
    public function extendedExpiry(): ?string
    {
        if ($this->subscriptionAction !== 'extend') {
            return null;
        }

        $active = $this->activeSubscription;
        $from = $active->expires_at?->isFuture() ? $active->expires_at : now();

        return match ($this->plan->validity_unit) {
            'Mins' => $from->copy()->addMinutes($this->plan->validity)->format('d M Y, H:i'),
            'Hrs' => $from->copy()->addHours($this->plan->validity)->format('d M Y, H:i'),
            'Days' => $from->copy()->addDays($this->plan->validity)->format('d M Y'),
            'Months' => $from->copy()->addMonths($this->plan->validity)->format('d M Y'),
            default => $from->copy()->addDays($this->plan->validity)->format('d M Y'),
        };
    }

    #[Computed]
    public function finalPrice(): float
    {
        return max(0.01, (float) $this->plan->price - $this->discountAmount);
    }

    public function render(): View
    {
        return view('livewire.portal.checkout');
    }
}
