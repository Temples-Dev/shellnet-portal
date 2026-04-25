<?php

namespace App\Services\RouterOS\Contracts;

use App\Models\Subscription;

interface DeviceDriverInterface
{
    /**
     * Provision a user on the router device for this subscription.
     */
    public function addUser(Subscription $subscription): void;

    /**
     * Remove the user from the router device.
     */
    public function removeUser(Subscription $subscription): void;

    /**
     * Check whether a user with this username already exists on the device.
     */
    public function userExists(string $username): bool;
}
