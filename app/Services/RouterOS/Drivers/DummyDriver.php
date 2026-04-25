<?php

namespace App\Services\RouterOS\Drivers;

use App\Models\Subscription;
use App\Services\RouterOS\Contracts\DeviceDriverInterface;
use Illuminate\Support\Facades\Log;

/**
 * No-op driver used when no real router is configured or during testing.
 */
class DummyDriver implements DeviceDriverInterface
{
    public function addUser(Subscription $subscription): void
    {
        Log::info('[DummyDriver] addUser', ['username' => $subscription->customer?->username]);
    }

    public function removeUser(Subscription $subscription): void
    {
        Log::info('[DummyDriver] removeUser', ['username' => $subscription->customer?->username]);
    }

    public function userExists(string $username): bool
    {
        return false;
    }
}
