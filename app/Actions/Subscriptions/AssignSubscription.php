<?php

namespace App\Actions\Subscriptions;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\LoyaltyCouponNotification;
use App\Notifications\PlanAssignedNotification;
use App\Services\RouterOS\RouterOSManager;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssignSubscription
{
    public function __construct(private readonly RouterOSManager $manager) {}

    /**
     * Assign or extend a subscription for the customer.
     *
     * Same plan → extend existing subscription's expiry.
     * Different plan (upgrade or new) → expire current, create fresh subscription.
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

        $active = Subscription::where('customer_id', $customer->id)
            ->where('status', 'on')
            ->latest()
            ->first();

        if ($active && $active->plan_id === $plan->id) {
            return $this->extendSubscription($active, $plan, $method, $admin, $customer, $rechargedAt);
        }

        return $this->createSubscription($active, $customer, $plan, $method, $admin, $rechargedAt);
    }

    // -------------------------------------------------------------------------
    // Extend (same plan)
    // -------------------------------------------------------------------------

    private function extendSubscription(
        Subscription $active,
        Plan $plan,
        string $method,
        ?User $admin,
        Customer $customer,
        CarbonInterface $rechargedAt
    ): Subscription {
        // Add validity on top of current expiry (or from now if already expired)
        $from = $active->expires_at?->isFuture() ? $active->expires_at : $rechargedAt;
        $newExpiry = $this->calculateExpiry($plan, $from);

        DB::transaction(function () use ($active, $plan, $method, $admin, $customer, $rechargedAt, $newExpiry): void {
            $active->update(['expires_at' => $newExpiry]);

            Transaction::create([
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_name' => $plan->name,
                'price' => $plan->price,
                'type' => $plan->type,
                'method' => $method,
                'router_id' => $plan->router_id,
                'router_name' => $plan->router?->name ?? '',
                'recharged_at' => $rechargedAt,
                'expires_at' => $newExpiry,
                'admin_id' => $admin?->id,
            ]);

            $customer->update(['status' => 'Active']);

            ActivityLog::record(
                'subscription_extended',
                "Plan [{$plan->name}] extended for [{$customer->username}] via {$method}, expires {$newExpiry->toDateTimeString()}",
                $admin?->id,
                $admin ? 'staff' : null,
                request()->ip(),
            );
        });

        // No MikroTik reprovision — same profile, user already exists on router
        $this->notifyCustomer($customer, $active);
        $this->awardLoyaltyCouponIfDue($customer);

        return $active;
    }

    // -------------------------------------------------------------------------
    // Create (new or upgrade)
    // -------------------------------------------------------------------------

    private function createSubscription(
        ?Subscription $active,
        Customer $customer,
        Plan $plan,
        string $method,
        ?User $admin,
        CarbonInterface $rechargedAt
    ): Subscription {
        $expiresAt = $this->calculateExpiry($plan, $rechargedAt);

        $subscription = DB::transaction(function () use ($active, $customer, $plan, $method, $admin, $rechargedAt, $expiresAt): Subscription {
            if ($active) {
                $active->update(['status' => 'expired']);
            }

            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'router_id' => $plan->router_id,
                'router_name' => $plan->router?->name ?? '',
                'type' => $plan->type,
                'method' => $method,
                'status' => 'on',
                'recharged_at' => $rechargedAt,
                'expires_at' => $expiresAt,
                'admin_id' => $admin?->id,
            ]);

            Transaction::create([
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_name' => $plan->name,
                'price' => $plan->price,
                'type' => $plan->type,
                'method' => $method,
                'router_id' => $plan->router_id,
                'router_name' => $plan->router?->name ?? '',
                'recharged_at' => $rechargedAt,
                'expires_at' => $expiresAt,
                'admin_id' => $admin?->id,
            ]);

            $customer->update(['status' => 'Active']);

            ActivityLog::record(
                'subscription_assigned',
                "Plan [{$plan->name}] assigned to [{$customer->username}] via {$method}",
                $admin?->id,
                $admin ? 'staff' : null,
                request()->ip(),
            );

            return $subscription;
        });

        $this->provision($subscription, $plan);
        $this->notifyCustomer($customer, $subscription);
        $this->awardLoyaltyCouponIfDue($customer);

        return $subscription;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function provision(Subscription $subscription, Plan $plan): void
    {
        try {
            $driver = $this->manager->driver($plan->router, $plan->type);
            $driver->addUser($subscription->load(['customer', 'plan']));
        } catch (\Throwable $e) {
            Log::error('[AssignSubscription] Router provisioning failed', [
                'subscription' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyCustomer(Customer $customer, Subscription $subscription): void
    {
        if (! $customer->email) {
            return;
        }
        try {
            $customer->notify(new PlanAssignedNotification($subscription));
        } catch (\Throwable $e) {
            Log::warning('[AssignSubscription] Notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function awardLoyaltyCouponIfDue(Customer $customer): void
    {
        if (! $customer->email) {
            return;
        }

        $total = $customer->subscriptions()->count();

        if ($total === 0 || $total % 5 !== 0) {
            return;
        }

        try {
            $coupon = Coupon::create([
                'code' => 'LOYAL-'.strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'type' => 'percent',
                'value' => 10,
                'description' => "Loyalty reward for {$total} subscriptions",
                'max_usage' => 1,
                'usage_count' => 0,
                'status' => 'active',
                'expires_at' => now()->addDays(30)->toDateString(),
            ]);

            $customer->notify(new LoyaltyCouponNotification($coupon, $total));
        } catch (\Throwable $e) {
            Log::warning('[AssignSubscription] Loyalty coupon failed', ['error' => $e->getMessage()]);
        }
    }

    private function calculateExpiry(Plan $plan, CarbonInterface $from): CarbonInterface
    {
        return match ($plan->validity_unit) {
            'Mins' => $from->copy()->addMinutes($plan->validity),
            'Hrs' => $from->copy()->addHours($plan->validity),
            'Days' => $from->copy()->addDays($plan->validity),
            'Months' => $from->copy()->addMonths($plan->validity),
            default => $from->copy()->addDays($plan->validity),
        };
    }
}
