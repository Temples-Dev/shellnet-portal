<?php

namespace App\Actions\Subscriptions;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RouterOS\RouterOSManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignSubscription
{
    public function __construct(private readonly RouterOSManager $manager) {}

    /**
     * Create a subscription + transaction record, then provision the user on the router.
     *
     * @throws \Throwable
     */
    public function execute(
        Customer $customer,
        Plan $plan,
        string $method = 'cash',
        ?User $admin = null
    ): Subscription {
        $rechargedAt = now();
        $expiresAt   = $this->calculateExpiry($plan, $rechargedAt);

        $subscription = DB::transaction(function () use ($customer, $plan, $method, $admin, $rechargedAt, $expiresAt): Subscription {
            // Expire any existing active subscription for this customer
            Subscription::where('customer_id', $customer->id)
                ->where('status', 'on')
                ->update(['status' => 'expired']);

            $subscription = Subscription::create([
                'customer_id'  => $customer->id,
                'plan_id'      => $plan->id,
                'plan_name'    => $plan->name,
                'router_id'    => $plan->router_id,
                'router_name'  => $plan->router?->name ?? '',
                'type'         => $plan->type,
                'method'       => $method,
                'status'       => 'on',
                'recharged_at' => $rechargedAt,
                'expires_at'   => $expiresAt,
                'admin_id'     => $admin?->id,
            ]);

            Transaction::create([
                'customer_id'  => $customer->id,
                'username'     => $customer->username,
                'plan_name'    => $plan->name,
                'price'        => $plan->price,
                'type'         => $plan->type,
                'method'       => $method,
                'router_id'    => $plan->router_id,
                'router_name'  => $plan->router?->name ?? '',
                'recharged_at' => $rechargedAt,
                'expires_at'   => $expiresAt,
                'admin_id'     => $admin?->id,
            ]);

            // Ensure customer is marked Active
            $customer->update(['status' => 'Active']);

            ActivityLog::record(
                'subscription_assigned',
                "Plan [{$plan->name}] assigned to [{$customer->username}] via {$method}",
                $admin?->id,
                'admin',
                request()->ip(),
            );

            return $subscription;
        });

        // Provision on router outside the transaction so a connection failure
        // doesn't roll back the billing record
        $this->provision($subscription, $plan);

        return $subscription;
    }

    private function provision(Subscription $subscription, Plan $plan): void
    {
        try {
            $driver = $this->manager->driver($plan->router, $plan->type);
            $driver->addUser($subscription->load('customer'));
        } catch (\Throwable $e) {
            Log::error('[AssignSubscription] Router provisioning failed', [
                'subscription' => $subscription->id,
                'error'        => $e->getMessage(),
            ]);
            // Non-fatal: billing record already persisted
        }
    }

    private function calculateExpiry(Plan $plan, \Carbon\Carbon $from): \Carbon\Carbon
    {
        return match ($plan->validity_unit) {
            'Mins'   => $from->copy()->addMinutes($plan->validity),
            'Hrs'    => $from->copy()->addHours($plan->validity),
            'Days'   => $from->copy()->addDays($plan->validity),
            'Months' => $from->copy()->addMonths($plan->validity),
            default  => $from->copy()->addDays($plan->validity),
        };
    }
}
