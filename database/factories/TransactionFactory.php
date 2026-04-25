<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first() ?? Customer::factory()->create();
        $plan     = Plan::inRandomOrder()->first() ?? Plan::factory()->create();
        $router   = Router::find($plan->router_id);

        return [
            'customer_id'  => $customer->id,
            'username'     => $customer->username,
            'plan_name'    => $plan->name,
            'price'        => $plan->price,
            'type'         => $plan->type,
            'method'       => $this->faker->randomElement(['cash', 'mpesa', 'card', 'voucher']),
            'router_id'    => $plan->router_id,
            'router_name'  => $router?->name ?? 'Main Router',
            'recharged_at' => $this->faker->dateTimeBetween('-12 months', 'now'),
            'expires_at'   => $this->faker->dateTimeBetween('now', '+3 months'),
            'created_at'   => $this->faker->dateTimeBetween('-12 months', 'now'),
        ];
    }
}
