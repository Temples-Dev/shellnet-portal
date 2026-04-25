<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('subscriptions:expire')->hourly();

Schedule::command('subscriptions:notify-expiring --days=3')->dailyAt('08:00');
