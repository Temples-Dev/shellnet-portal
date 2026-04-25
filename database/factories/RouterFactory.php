<?php

namespace Database\Factories;

use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Router>
 */
class RouterFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['Online', 'Offline']);

        return [
            'name'        => $this->faker->unique()->words(2, true),
            'ip_address'  => $this->faker->localIpv4() . ':8728',
            'username'    => 'admin',
            'password'    => 'admin',
            'description' => $this->faker->optional()->sentence(),
            'coordinates' => $this->faker->latitude(-10, -1) . ',' . $this->faker->longitude(33, 42),
            'status'      => $status,
            'last_seen'   => $status === 'Online' ? now()->subMinutes(rand(1, 10)) : now()->subHours(rand(1, 48)),
            'enabled'     => true,
        ];
    }

    public function online(): static
    {
        return $this->state(['status' => 'Online', 'last_seen' => now()->subMinutes(2)]);
    }

    public function offline(): static
    {
        return $this->state(['status' => 'Offline', 'last_seen' => now()->subHours(6)]);
    }
}
