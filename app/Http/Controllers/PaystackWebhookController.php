<?php

namespace App\Http\Controllers;

use App\Actions\Subscriptions\AssignSubscription;
use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\Paystack\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __construct(
        private readonly PaystackService     $paystack,
        private readonly AssignSubscription  $assignSubscription,
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Paystack-Signature', '');

        if (! $this->paystack->validateWebhookSignature($payload, $signature)) {
            Log::warning('[PaystackWebhook] Invalid signature');
            return response('Unauthorized', 401);
        }

        $event = $request->json('event');
        $data  = $request->json('data', []);

        if ($event === 'charge.success') {
            $this->handleChargeSuccess($data);
        }

        return response('OK', 200);
    }

    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;
        $meta      = $data['metadata'] ?? [];

        if (! $reference) return;

        // Idempotency — skip if already processed
        if (PaymentTransaction::where('reference', $reference)->where('status', PaymentTransaction::STATUS_PAID)->exists()) {
            return;
        }

        $customerId = $meta['customer_id'] ?? null;
        $planId     = $meta['plan_id'] ?? null;

        if (! $customerId || ! $planId) {
            Log::error('[PaystackWebhook] Missing metadata', ['reference' => $reference]);
            return;
        }

        $customer = Customer::find($customerId);
        $plan     = Plan::find($planId);

        if (! $customer || ! $plan) {
            Log::error('[PaystackWebhook] Customer or plan not found', compact('customerId', 'planId'));
            return;
        }

        try {
            $subscription = $this->assignSubscription->execute($customer, $plan, 'paystack');

            PaymentTransaction::updateOrCreate(
                ['reference' => $reference],
                [
                    'customer_id'     => $customer->id,
                    'username'        => $customer->username,
                    'plan_id'         => $plan->id,
                    'plan_name'       => $plan->name,
                    'invoice'         => $subscription->transactions()->latest()->first()?->invoice,
                    'price'           => ($data['amount'] ?? 0) / 100,
                    'currency'        => $data['currency'] ?? 'NGN',
                    'gateway'         => 'paystack',
                    'payment_method'  => 'paystack',
                    'status'          => PaymentTransaction::STATUS_PAID,
                    'gateway_trx_id'  => (string) ($data['id'] ?? ''),
                    'paid_at'         => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('[PaystackWebhook] AssignSubscription failed', [
                'reference' => $reference,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
