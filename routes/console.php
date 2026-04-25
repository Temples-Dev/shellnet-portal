<?php

use Illuminate\Support\Facades\Schedule;

// Expire subscriptions every hour; a lightweight check means running hourly is fine
Schedule::command('subscriptions:expire')->hourly();
