<?php

namespace App\Actions\Subscriptions;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Subscription;
use App\Services\RouterOS\RouterOSManager;
use Illuminate\Support\Facades\Log;

class RevokeSubscription
{
    public function __construct(private readonly RouterOSManager $manager) {}

    /**
     * Mark a subscription as expired and remove the user from the router.
     */
    public function execute(Subscription $subscription): void
    {
        $subscription->load(['customer', 'router', 'plan']);

        $subscription->update(['status' => 'expired']);

        // Flip customer Inactive if they have no remaining active subscription
        $customer = $subscription->customer;
        if ($customer && ! Customer::find($customer->id)?->activeSubscription()->exists()) {
            $customer->update(['status' => 'Inactive']);
        }

        ActivityLog::record(
            'subscription_revoked',
            "Subscription #{$subscription->id} revoked (plan: {$subscription->plan_name})",
            null,
        );

        $this->deprovision($subscription);
    }

    private function deprovision(Subscription $subscription): void
    {
        try {
            $router = $subscription->router;
            $type   = $subscription->plan?->type ?? $subscription->type;
            $driver = $this->manager->driver($router, $type);
            $driver->removeUser($subscription);
        } catch (\Throwable $e) {
            Log::error('[RevokeSubscription] Router de-provisioning failed', [
                'subscription' => $subscription->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
