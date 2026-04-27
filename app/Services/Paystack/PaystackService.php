<?php

namespace App\Services\Paystack;

use App\Models\Customer;
use App\Models\Plan;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key', '');
    }

    // -------------------------------------------------------------------------
    // Initialize
    // -------------------------------------------------------------------------

    /**
     * Create a Paystack payment session and return the authorization URL.
     *
     * @param  float|null  $overrideAmount  Charged amount (post-discount). Defaults to plan price.
     * @param  array       $extraMetadata   Merged into transaction metadata (e.g. coupon_id).
     * @return array{authorization_url: string, access_code: string, reference: string}
     * @throws \RuntimeException
     */
    public function initializeTransaction(
        Customer $customer,
        Plan $plan,
        string $callbackUrl,
        ?string $reference = null,
        ?float $overrideAmount = null,
        array $extraMetadata = []
    ): array {
        $reference ??= 'PS-' . strtoupper(Str::random(12));

        $email = $customer->email
            ?? preg_replace('/\D/', '', $customer->phone ?? '') . '@customers.shellnet.app';

        $amount = $overrideAmount ?? (float) $plan->price;

        $response = $this->post('/transaction/initialize', [
            'email'        => $email,
            'amount'       => (int) round($amount * 100),
            'currency'     => config('services.paystack.currency', 'GHS'),
            'reference'    => $reference,
            'callback_url' => $callbackUrl,
            'metadata'     => array_merge([
                'customer_id' => $customer->id,
                'plan_id'     => $plan->id,
                'plan_name'   => $plan->name,
                'username'    => $customer->username,
            ], $extraMetadata),
        ]);

        if (! $response->successful() || ! $response->json('status')) {
            throw new \RuntimeException(
                'Paystack initialization failed: ' . $response->json('message', 'Unknown error')
            );
        }

        return $response->json('data');
    }

    // -------------------------------------------------------------------------
    // Verify
    // -------------------------------------------------------------------------

    /**
     * Verify a transaction by reference. Returns the full data array on success.
     *
     * @throws \RuntimeException
     */
    public function verifyTransaction(string $reference): array
    {
        $response = $this->get("/transaction/verify/{$reference}");

        if (! $response->successful() || ! $response->json('status')) {
            throw new \RuntimeException(
                'Paystack verification failed: ' . $response->json('message', 'Unknown error')
            );
        }

        $data = $response->json('data');

        if ($data['status'] !== 'success') {
            throw new \RuntimeException('Transaction not successful. Status: ' . $data['status']);
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // Webhook
    // -------------------------------------------------------------------------

    /**
     * Validate the HMAC-SHA512 webhook signature from Paystack.
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($expected, $signature);
    }

    // -------------------------------------------------------------------------
    // HTTP helpers
    // -------------------------------------------------------------------------

    private function post(string $path, array $data): Response
    {
        return Http::withToken($this->secretKey)
            ->acceptJson()
            ->post($this->baseUrl . $path, $data);
    }

    private function get(string $path): Response
    {
        return Http::withToken($this->secretKey)
            ->acceptJson()
            ->get($this->baseUrl . $path);
    }
}
