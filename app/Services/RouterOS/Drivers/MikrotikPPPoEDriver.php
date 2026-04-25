<?php

namespace App\Services\RouterOS\Drivers;

use App\Models\Subscription;
use App\Services\RouterOS\Contracts\DeviceDriverInterface;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikPPPoEDriver implements DeviceDriverInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addUser(Subscription $subscription): void
    {
        $customer = $subscription->customer;
        $username = $customer->pppoe_username ?: $customer->username;
        $password = $customer->pppoe_password ?: $customer->username;

        // Remove stale entry first
        if ($this->userExists($username)) {
            $this->removeByUsername($username);
        }

        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $subscription->plan_name)
            ->equal('service', 'pppoe')
            ->equal('comment', 'sub:' . $subscription->id);

        // If customer has a static PPPoE IP, assign it
        if ($customer->pppoe_ip) {
            $query->equal('remote-address', $customer->pppoe_ip);
        }

        $this->client->query($query)->read();
    }

    public function removeUser(Subscription $subscription): void
    {
        $customer = $subscription->customer;
        $username = $customer?->pppoe_username ?: $customer?->username;
        if ($username) {
            $this->removeByUsername($username);
        }
    }

    public function userExists(string $username): bool
    {
        $result = $this->client
            ->query('/ppp/secret/print', [['name', '=', $username]])
            ->read();

        return ! empty($result);
    }

    private function removeByUsername(string $username): void
    {
        $result = $this->client
            ->query('/ppp/secret/print', [['name', '=', $username]])
            ->read();

        foreach ($result as $entry) {
            if (isset($entry['.id'])) {
                $this->client
                    ->query((new Query('/ppp/secret/remove'))->equal('.id', $entry['.id']))
                    ->read();
            }
        }
    }
}
