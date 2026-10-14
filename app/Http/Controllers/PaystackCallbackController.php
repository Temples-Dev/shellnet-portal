<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\Paystack\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackCallbackController extends Controller
{
    public function __construct(private readonly PaystackService $paystack) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $reference = $request->query('reference', '');

        if (! $reference) {
            return redirect()->route('billing.transactions')
                ->with('error', 'Invalid payment reference.');
        }

        try {
            $data = $this->paystack->verifyTransaction($reference);

            // Mark PaymentTransaction paid if webhook hasn't already
            PaymentTransaction::where('reference', $reference)
                ->where('status', '!=', PaymentTransaction::STATUS_PAID)
                ->update(['status' => PaymentTransaction::STATUS_PAID, 'paid_at' => now()]);

            return redirect()->route('billing.transactions')
                ->with('success', 'Payment confirmed. Subscription activated.');
        } catch (\Throwable $e) {
            Log::warning('[PaystackCallback] Verification failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('billing.transactions')
                ->with('error', 'Payment could not be verified: '.$e->getMessage());
        }
    }
}
