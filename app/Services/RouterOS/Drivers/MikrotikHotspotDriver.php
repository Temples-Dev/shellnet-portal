<?php

namespace App\Services\RouterOS\Drivers;

use App\Models\Subscription;
use App\Services\RouterOS\Contracts\DeviceDriverInterface;
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
        $plan = $subscription->plan;
        $username = $customer->username;
        $password = $customer->pppoe_password ?? $customer->username;

        // Remove stale entry if it already exists
        if ($this->userExists($username)) {
            $this->removeByUsername($username);
        }

        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $subscription->plan_name)
            ->equal('comment', 'sub:'.$subscription->id);

        // Bind to MAC address when set — prevents credential sharing
        if ($customer->mac_address) {
            $query->equal('mac-address', $customer->mac_address);
        }

        // Push plan limits when the plan is loaded and limited
        if ($plan && $plan->is_limited) {
            if (in_array($plan->limit_type, ['Time_Limit', 'Both_Limit'], true)) {
                $query->equal('limit-uptime', $this->formatUptime((int) $plan->time_limit, $plan->time_unit));
            }

            if (in_array($plan->limit_type, ['Data_Limit', 'Both_Limit'], true)) {
                $query->equal('limit-bytes-total', $this->formatBytes((int) $plan->data_limit, $plan->data_unit));
            }
        }

        if ($plan && $plan->shared_users) {
            $query->equal('shared-users', (string) $plan->shared_users);
        }

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

    // Format time limit as HH:MM:SS for MikroTik limit-uptime
    private function formatUptime(int $limit, string $unit): string
    {
        $seconds = match ($unit) {
            'Hrs' => $limit * 3600,
            'Mins' => $limit * 60,
            default => $limit * 60,
        };

        return sprintf(
            '%02d:%02d:%02d',
            intdiv($seconds, 3600),
            intdiv($seconds % 3600, 60),
            $seconds % 60
        );
    }

    // Format data limit as bytes string for MikroTik limit-bytes-total
    private function formatBytes(int $limit, string $unit): string
    {
        $bytes = match ($unit) {
            'GB' => $limit * 1073741824,
            'MB' => $limit * 1048576,
            default => $limit * 1048576,
        };

        return (string) $bytes;
    }
}
