<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('subscriptions:expire')->everyFiveMinutes();

Schedule::command('subscriptions:notify-expiring --days=3')->dailyAt('08:00');

Schedule::command('routers:check-uplink')->everyFiveMinutes();

// Auto-fail payment transactions whose 20-minute window has passed
Schedule::call(function () {
    \App\Models\PaymentTransaction::pending()
        ->where('expired_at', '<', now())
        ->update(['status' => \App\Models\PaymentTransaction::STATUS_FAILED]);
})->everyFiveMinutes()->name('payment-transactions:expire-pending')->withoutOverlapping();
