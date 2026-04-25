<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use App\Services\Paystack\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PortalPaymentController extends Controller
{
    public function __construct(private readonly PaystackService $paystack) {}

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

            session(['portal_pending_plan' => $plan->id, 'portal_customer_id' => $customer->id]);

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
            $this->paystack->verifyTransaction($reference);
            session()->flash('portal_success', true);
            return redirect()->route('portal.success');
        } catch (\Throwable $e) {
            Log::warning('[PortalPayment] Callback verify failed', ['reference' => $reference, 'error' => $e->getMessage()]);
            return redirect()->route('portal.index')->withErrors(['payment' => 'Payment could not be verified: ' . $e->getMessage()]);
        }
    }

    private function findOrCreateCustomer(array $data): Customer
    {
        $existing = Customer::where('phone', $data['phone'])->first();

        if ($existing) {
            return $existing;
        }

        $username = 'c' . preg_replace('/\D/', '', $data['phone']);
        $password = Str::random(8);

        $customer = Customer::create([
            'fullname'     => $data['fullname'],
            'username'     => $username,
            'phone'        => $data['phone'],
            'email'        => $data['email'] ?? null,
            'password'     => Hash::make($password),
            'status'       => 'Active',
            'service_type' => 'Hotspot',
            'account_type' => 'Personal',
        ]);

        session(['portal_new_password' => $password, 'portal_username' => $username]);

        return $customer;
    }
}
