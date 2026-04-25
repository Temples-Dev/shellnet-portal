<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:notify-expiring {--days=3 : Notify subscriptions expiring within this many days}')]
#[Description('Send expiry warning emails to customers whose subscriptions are expiring soon.')]
class NotifyExpiringSubscriptions extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $expiring = Subscription::active()
            ->whereBetween('expires_at', [now(), now()->addDays($days)->endOfDay()])
            ->with('customer')
            ->get();

        if ($expiring->isEmpty()) {
            $this->info("No subscriptions expiring within {$days} day(s).");
            return self::SUCCESS;
        }

        foreach ($expiring as $subscription) {
            $customer = $subscription->customer;
            if (! $customer || ! $customer->email) {
                continue;
            }

            $daysLeft = (int) now()->diffInDays($subscription->expires_at);
            $customer->notify(new SubscriptionExpiringNotification($subscription, $daysLeft));
        }

        $this->info("Notified {$expiring->count()} customer(s).");
        return self::SUCCESS;
    }
}
