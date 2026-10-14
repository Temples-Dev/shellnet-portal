<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $plan = Plan::inRandomOrder()->first() ?? Plan::factory()->create();
        $rechargedAt = $this->faker->dateTimeBetween('-3 months', 'now');
        $expiresAt = (clone $rechargedAt)->modify("+{$plan->validity} days");
        $status = $expiresAt > now() ? 'on' : 'expired';

        return [
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'router_id' => $plan->router_id,
            'router_name' => Router::find($plan->router_id)?->name ?? 'Main Router',
            'type' => $plan->type,
            'method' => $this->faker->randomElement(['cash', 'card', 'paystack', 'voucher']),
            'status' => $status,
            'recharged_at' => $rechargedAt,
            'expires_at' => $expiresAt,
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes): array {
            $rechargedAt = now()->subDays(rand(1, 20));

            return [
                'status' => 'on',
                'recharged_at' => $rechargedAt,
                'expires_at' => $rechargedAt->copy()->addDays(30),
            ];
        });
    }
}
