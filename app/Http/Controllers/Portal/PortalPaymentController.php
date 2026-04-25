<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Subscriptions\AssignSubscription;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\Paystack\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PortalPaymentController extends Controller
{
    public function __construct(
        private readonly PaystackService   $paystack,
        private readonly AssignSubscription $assignSubscription,
    ) {}

    public function pay(Request $request, Plan $plan): RedirectResponse
    {
        abort_if(! $plan->enabled, 404);

        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'phone'    => ['required', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:100'],
        ]);

        $customer = $this->findOrCreateCustomer($data);

        try {
            $result = $this->paystack->initializeTransaction(
                $customer,
                $plan,
                route('portal.payment.callback'),
            );

            session([
                'portal_pending_plan'     => $plan->id,
                'portal_customer_id'      => $customer->id,
                'portal_payment_reference' => $result['reference'],
            ]);

            return redirect()->away($result['authorization_url']);
        } catch (\Throwable $e) {
            Log::error('[PortalPayment] Init failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['payment' => 'Could not initiate payment. Please try again.']);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference', '');

        if (! $reference) {
            return redirect()->route('portal.index')->withErrors(['payment' => 'Invalid payment reference.']);
        }

        try {
            $data = $this->paystack->verifyTransaction($reference);

            // Idempotency — skip if webhook already processed this
            $alreadyProcessed = PaymentTransaction::where('reference', $reference)
                ->where('status', PaymentTransaction::STATUS_PAID)
                ->exists();

            if (! $alreadyProcessed) {
                $this->processPayment($reference, $data);
            }

            session()->flash('portal_success', true);
            return redirect()->route('portal.success');
        } catch (\Throwable $e) {
            Log::warning('[PortalPayment] Callback failed', ['reference' => $reference, 'error' => $e->getMessage()]);
            return redirect()->route('portal.index')->withErrors(['payment' => 'Payment could not be verified. Please contact support.']);
        }
    }

    private function processPayment(string $reference, array $data): void
    {
        $meta       = $data['metadata'] ?? [];
        $customerId = $meta['customer_id'] ?? session('portal_customer_id');
        $planId     = $meta['plan_id'] ?? session('portal_pending_plan');

        $customer = Customer::find($customerId);
        $plan     = Plan::find($planId);

        if (! $customer || ! $plan) {
            Log::error('[PortalPayment] Customer or plan not found in callback', compact('customerId', 'planId'));
            return;
        }

        $subscription = $this->assignSubscription->execute($customer, $plan, 'paystack');

        PaymentTransaction::updateOrCreate(
            ['reference' => $reference],
            [
                'customer_id'    => $customer->id,
                'username'       => $customer->username,
                'plan_id'        => $plan->id,
                'plan_name'      => $plan->name,
                'invoice'        => $subscription->transactions()->latest()->first()?->invoice,
                'price'          => ($data['amount'] ?? 0) / 100,
                'currency'       => $data['currency'] ?? 'GHS',
                'gateway'        => 'paystack',
                'payment_method' => 'paystack',
                'status'         => PaymentTransaction::STATUS_PAID,
                'gateway_trx_id' => (string) ($data['id'] ?? ''),
                'paid_at'        => now(),
            ]
        );
    }

    private function findOrCreateCustomer(array $data): Customer
    {
        $existing = Customer::where('phone', $data['phone'])->first();

        if ($existing) {
            return $existing;
        }

        $username = 'c' . preg_replace('/\D/', '', $data['phone']);
        $password = strtoupper(Str::random(4)) . rand(10, 99);

        $customer = Customer::create([
            'fullname'       => $data['fullname'],
            'username'       => $username,
            'phone'          => $data['phone'],
            'email'          => $data['email'] ?? null,
            'password'       => Hash::make($password),
            'pppoe_password' => $password,
            'status'         => 'Active',
            'service_type'   => 'Hotspot',
            'account_type'   => 'Personal',
        ]);

        session([
            'portal_new_credentials' => [
                'username' => $username,
                'password' => $password,
                'phone'    => $data['phone'],
            ],
        ]);

        return $customer;
    }
}
