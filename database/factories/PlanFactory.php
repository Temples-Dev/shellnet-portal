<?php

namespace Database\Factories;

use App\Models\Bandwidth;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(Plan::TYPES);
        $validity = $this->faker->randomElement([1, 7, 30, 90]);
        $price = match (true) {
            $validity >= 90 => $this->faker->randomFloat(2, 50, 200),
            $validity >= 30 => $this->faker->randomFloat(2, 15, 80),
            $validity >= 7 => $this->faker->randomFloat(2, 5, 25),
            default => $this->faker->randomFloat(2, 1, 10),
        };

        return [
            'name' => "{$type} {$validity}D",
            'bandwidth_id' => Bandwidth::inRandomOrder()->first()?->id ?? Bandwidth::factory(),
            'price' => $price,
            'type' => $type,
            'is_limited' => false,
            'limit_type' => 'Time_Limit',
            'validity' => $validity,
            'validity_unit' => 'Days',
            'shared_users' => 1,
            'router_id' => Router::inRandomOrder()->first()?->id ?? Router::factory(),
            'is_radius' => false,
            'enabled' => true,
            'prepaid' => true,
            'plan_type' => 'Personal',
            'device' => 'mikrotik',
        ];
    }
}
