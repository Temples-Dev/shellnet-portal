<?php

namespace App\Services\RouterOS\Drivers;

use App\Models\Subscription;
use App\Services\RouterOS\Contracts\DeviceDriverInterface;
use App\Services\RouterOS\RouterOSClientFactory;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikHotspotDriver implements DeviceDriverInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addUser(Subscription $subscription): void
    {
        $customer = $subscription->customer;
        $username = $customer->username;
        $password = $customer->pppoe_password ?? $customer->username; // hotspot password

        // Remove stale entry if it already exists
        if ($this->userExists($username)) {
            $this->removeByUsername($username);
        }

        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $subscription->plan_name)
            ->equal('comment', 'sub:' . $subscription->id);

        $this->client->query($query)->read();
    }

    public function removeUser(Subscription $subscription): void
    {
        $username = $subscription->customer?->username;
        if ($username) {
            $this->removeByUsername($username);
        }
    }

    public function userExists(string $username): bool
    {
        $result = $this->client
            ->query('/ip/hotspot/user/print', [['name', '=', $username]])
            ->read();

        return ! empty($result);
    }

    private function removeByUsername(string $username): void
    {
        $result = $this->client
            ->query('/ip/hotspot/user/print', [['name', '=', $username]])
            ->read();

        foreach ($result as $entry) {
            if (isset($entry['.id'])) {
                $this->client
                    ->query((new Query('/ip/hotspot/user/remove'))->equal('.id', $entry['.id']))
                    ->read();
            }
        }
    }
}
