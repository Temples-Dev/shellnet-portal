<?php

namespace App\Console\Commands;

use App\Actions\Subscriptions\RevokeSubscription;
use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:expire')]
#[Description('Mark active subscriptions whose expiry date has passed as expired and remove users from routers.')]
class ExpireSubscriptions extends Command
{
    public function handle(RevokeSubscription $revoke): int
    {
        $expired = Subscription::active()
            ->where('expires_at', '<=', now())
            ->with(['customer', 'router', 'plan'])
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No subscriptions to expire.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($expired->count());
        $bar->start();

        foreach ($expired as $subscription) {
            $revoke->execute($subscription);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Expired {$expired->count()} subscription(s).");

        return self::SUCCESS;
    }
}
