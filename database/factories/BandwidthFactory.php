<?php

namespace Database\Factories;

use App\Models\Bandwidth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bandwidth>
 */
class BandwidthFactory extends Factory
{
    public function definition(): array
    {
        $down = $this->faker->randomElement([1, 2, 5, 10, 20, 50]);
        $up   = (int) ($down / 2) ?: 1;

        return [
            'name'           => "{$down}Mbps/{$up}Mbps",
            'rate_down'      => $down,
            'rate_down_unit' => 'Mbps',
            'rate_up'        => $up,
            'rate_up_unit'   => 'Mbps',
            'burst'          => null,
        ];
    }
}
