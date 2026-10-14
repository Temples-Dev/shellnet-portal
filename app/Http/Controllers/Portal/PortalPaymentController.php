<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Subscriptions\AssignSubscription;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\Paystack\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PortalPaymentController extends Controller
{
    public function __construct(
        private readonly PaystackService $paystack,
        private readonly AssignSubscription $assignSubscription,
    ) {}

    public function pay(Request $request, Plan $plan): RedirectResponse
    {
        abort_if(! $plan->enabled, 404);

        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $data = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        // Enforce upgrade-only: block downgrade attempts
        $active = Subscription::where('customer_id', $customer->id)
            ->where('status', 'on')
            ->latest()
            ->first();

        if ($active && $active->plan_id !== $plan->id) {
            $activePlan = Plan::find($active->plan_id);
            if ($activePlan && (float) $plan->price < (float) $activePlan->price) {
                return back()->withErrors([
                    'payment' => "Cannot downgrade from {$activePlan->name}. Please choose a plan of equal or greater value.",
                ]);
            }
        }

        // Resolve coupon
        $coupon = null;
        $discountAmount = 0.0;
        $originalPrice = (float) $plan->price;
        $finalPrice = $originalPrice;

        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::active()
                ->where('code', strtoupper(trim($data['coupon_code'])))
                ->first();

            if ($coupon && $coupon->is_valid) {
                $lockedToOther = $coupon->customer_id && $coupon->customer_id !== $customer->id;
                $meetsMinimum = $coupon->min_order_amount === null
                    || $originalPrice >= (float) $coupon->min_order_amount;

                if (! $lockedToOther && $meetsMinimum) {
                    $discountAmount = $coupon->calculateDiscount($originalPrice);
                    $finalPrice = max(0.01, $originalPrice - $discountAmount);
                }
            }
        }

        // Idempotency: reuse an existing unexpired pending tx for this customer+plan
        $existing = PaymentTransaction::where('customer_id', $customer->id)
            ->where('plan_id', $plan->id)
            ->where('status', PaymentTransaction::STATUS_UNPAID)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if ($existing && $existing->pg_url) {
            return redirect()->away($existing->pg_url);
        }

        try {
            $result = $this->paystack->initializeTransaction(
                $customer,
                $plan,
                route('portal.payment.callback'),
                null,
                $finalPrice,
                [
                    'coupon_id' => $coupon?->id,
                    'discount_amount' => $discountAmount,
                    'original_price' => $originalPrice,
                ],
            );

            // Create a pending payment record immediately so the customer has history
            // and so we can auto-fail if they abandon the payment
            PaymentTransaction::create([
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'reference' => $result['reference'],
                'gateway' => 'paystack',
                'payment_method' => 'paystack',
                'price' => $finalPrice,
                'original_price' => $originalPrice,
                'discount_amount' => $discountAmount,
                'coupon_id' => $coupon?->id,
                'currency' => config('services.paystack.currency', 'GHS'),
                'status' => PaymentTransaction::STATUS_UNPAID,
                'pg_url' => $result['authorization_url'],
                'expired_at' => now()->addMinutes(20),
            ]);

            return redirect()->away($result['authorization_url']);
        } catch (\Throwable $e) {
            Log::error('[PortalPayment] Init failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['payment' => 'Could not initiate payment. Please try again.']);
        }
    }

    public function retry(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        abort_if($transaction->customer_id !== $customer->id, 403);
        abort_if((int) $transaction->status !== PaymentTransaction::STATUS_FAILED, 422);

        $plan = Plan::find($transaction->plan_id);
        abort_if(! $plan || ! $plan->enabled, 404);

        // Re-verify the failed reference with Paystack first.
        // The payment may have succeeded on their side but our processPayment crashed.
        // If so, process it now instead of creating a duplicate session.
        try {
            $data = $this->paystack->verifyTransaction($transaction->reference);
            $alreadyProcessed = PaymentTransaction::where('reference', $transaction->reference)
                ->where('status', PaymentTransaction::STATUS_PAID)
                ->exists();
            if (! $alreadyProcessed) {
                $this->processPayment($transaction->reference, $data);
            }

            return redirect()->route('portal.success');
        } catch (\Throwable $e) {
            // Paystack confirms not paid — fall through to create a new session
        }

        // Idempotency: reuse an existing live pending tx for the same plan
        $existing = PaymentTransaction::where('customer_id', $customer->id)
            ->where('plan_id', $plan->id)
            ->where('status', PaymentTransaction::STATUS_UNPAID)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if ($existing && $existing->pg_url) {
            return redirect()->away($existing->pg_url);
        }

        try {
            $result = $this->paystack->initializeTransaction(
                $customer,
                $plan,
                route('portal.payment.callback'),
                null,
                (float) $transaction->price,
                [
                    'coupon_id' => $transaction->coupon_id,
                    'discount_amount' => (float) $transaction->discount_amount,
                    'original_price' => (float) $transaction->original_price,
                ],
            );

            PaymentTransaction::create([
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'reference' => $result['reference'],
                'gateway' => 'paystack',
                'payment_method' => 'paystack',
                'price' => (float) $transaction->price,
                'original_price' => (float) $transaction->original_price,
                'discount_amount' => (float) $transaction->discount_amount,
                'coupon_id' => $transaction->coupon_id,
                'currency' => $transaction->currency ?? config('services.paystack.currency', 'GHS'),
                'status' => PaymentTransaction::STATUS_UNPAID,
                'pg_url' => $result['authorization_url'],
                'expired_at' => now()->addMinutes(20),
            ]);

            return redirect()->away($result['authorization_url']);
        } catch (\Throwable $e) {
            Log::error('[PortalPayment] Retry failed', ['transaction' => $transaction->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['payment' => 'Could not initiate retry. Please try again.']);
        }
    }

    public function resume(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        abort_if($transaction->customer_id !== $customer->id, 403);

        // Accept both UNPAID (pending) and FAILED — either may have been paid on Paystack
        $resumable = in_array((int) $transaction->status, [
            PaymentTransaction::STATUS_UNPAID,
            PaymentTransaction::STATUS_FAILED,
        ]);
        abort_if(! $resumable, 422);

        // Step 1: check with Paystack whether money was actually taken
        try {
            $data = $this->paystack->verifyTransaction($transaction->reference);
        } catch (\Throwable $e) {
            // Paystack says not paid — safe to mark failed and offer retry
            PaymentTransaction::where('id', $transaction->id)
                ->where('status', PaymentTransaction::STATUS_UNPAID)
                ->update(['status' => PaymentTransaction::STATUS_FAILED, 'pg_url' => null]);

            return redirect()->route('portal.account')
                ->withErrors(['payment' => 'This payment was not completed. Use the Retry button to start a fresh payment.']);
        }

        // Step 2: payment confirmed by Paystack — process our side independently
        // Never mark failed here; the subscription may already be committed in the DB
        $alreadyProcessed = PaymentTransaction::where('reference', $transaction->reference)
            ->where('status', PaymentTransaction::STATUS_PAID)
            ->exists();

        if (! $alreadyProcessed) {
            try {
                $this->processPayment($transaction->reference, $data);
            } catch (\Throwable $e) {
                Log::error('[PortalPayment] processPayment failed in resume', [
                    'reference' => $transaction->reference,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->route('portal.account')
                    ->with('portal_processing', true);
            }
        }

        return redirect()->route('portal.success');
    }

    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference', '');

        if (! $reference) {
            return redirect()->route('portal.index')->withErrors(['payment' => 'Invalid payment reference.']);
        }

        // Step 1: verify with Paystack — did money actually change hands?
        try {
            $data = $this->paystack->verifyTransaction($reference);
        } catch (\Throwable $e) {
            Log::warning('[PortalPayment] Verification failed', ['reference' => $reference, 'error' => $e->getMessage()]);

            // Payment not confirmed by Paystack — safe to mark failed and let customer retry
            PaymentTransaction::where('reference', $reference)
                ->where('status', PaymentTransaction::STATUS_UNPAID)
                ->update(['status' => PaymentTransaction::STATUS_FAILED]);

            return redirect()->route('portal.account')
                ->withErrors(['payment' => 'Payment was not completed. You can retry below.']);
        }

        // Step 2: payment confirmed — process our side
        // Do NOT mark failed here even if our processing throws; money was taken and the webhook will retry
        $alreadyProcessed = PaymentTransaction::where('reference', $reference)
            ->where('status', PaymentTransaction::STATUS_PAID)
            ->exists();

        if (! $alreadyProcessed) {
            try {
                $this->processPayment($reference, $data);
            } catch (\Throwable $e) {
                Log::error('[PortalPayment] processPayment failed after confirmed payment', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->route('portal.account')
                    ->with('portal_processing', true);
            }
        }

        return redirect()->route('portal.success');
    }

    private function processPayment(string $reference, array $data): void
    {
        $meta = $data['metadata'] ?? [];
        $customerId = $meta['customer_id'] ?? null;
        $planId = $meta['plan_id'] ?? null;

        // Fall back to the pending record if metadata is missing
        if (! $customerId || ! $planId) {
            $pending = PaymentTransaction::where('reference', $reference)->first();
            $customerId = $customerId ?? $pending?->customer_id;
            $planId = $planId ?? $pending?->plan_id;
        }

        $customer = Customer::find($customerId);
        $plan = Plan::find($planId);

        if (! $customer || ! $plan) {
            Log::error('[PortalPayment] Customer or plan not found in callback', compact('customerId', 'planId'));

            return;
        }

        $subscription = $this->assignSubscription->execute($customer, $plan, 'paystack');

        $couponId = $meta['coupon_id'] ?? null;
        $discountAmount = (float) ($meta['discount_amount'] ?? 0);
        $originalPrice = (float) ($meta['original_price'] ?? ($data['amount'] ?? 0) / 100);

        // Fall back to the pending record values if Paystack metadata is missing
        $pending = PaymentTransaction::where('reference', $reference)->first();
        if ($pending) {
            $couponId = $couponId ?? $pending->coupon_id;
            $discountAmount = $discountAmount ?: (float) $pending->discount_amount;
            $originalPrice = $originalPrice ?: (float) $pending->original_price;
        }

        PaymentTransaction::updateOrCreate(
            ['reference' => $reference],
            [
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'invoice' => Transaction::where('customer_id', $customer->id)->latest()->value('invoice'),
                'coupon_id' => $couponId ?: null,
                'discount_amount' => $discountAmount,
                'original_price' => $originalPrice,
                'price' => ($data['amount'] ?? 0) / 100,
                'currency' => $data['currency'] ?? 'GHS',
                'gateway' => 'paystack',
                'payment_method' => 'paystack',
                'status' => PaymentTransaction::STATUS_PAID,
                'gateway_trx_id' => (string) ($data['id'] ?? ''),
                'pg_url' => null,
                'paid_at' => now(),
            ]
        );

        if ($couponId) {
            Coupon::where('id', $couponId)->increment('usage_count');
        }
    }
}
