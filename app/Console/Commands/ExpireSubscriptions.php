<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:expire')]
#[Description('Mark active subscriptions whose expiry date has passed as expired, and update the parent customer status.')]
class ExpireSubscriptions extends Command
{
    public function handle(): int
    {
        $expired = Subscription::active()
            ->where('expires_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No subscriptions to expire.');
            return self::SUCCESS;
        }

        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);

            // Mark the customer Inactive if they have no other active subscription
            $customer = Customer::find($subscription->customer_id);
            if ($customer && ! $customer->activeSubscription()->exists()) {
                $customer->update(['status' => 'Inactive']);
            }

            ActivityLog::record(
                'subscription_expired',
                "Subscription #{$subscription->id} expired (plan: {$subscription->plan_name})",
                $subscription->customer_id
            );
        }

        $this->info("Expired {$expired->count()} subscription(s).");

        return self::SUCCESS;
    }
}
