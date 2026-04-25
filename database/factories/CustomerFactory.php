<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        $fullname = $this->faker->name();
        $username = Str::lower(str_replace(' ', '.', $fullname)) . $this->faker->numerify('##');

        return [
            'username'     => $username,
            'password'     => bcrypt('password'),
            'fullname'     => $fullname,
            'email'        => $this->faker->unique()->safeEmail(),
            'phone'        => $this->faker->phoneNumber(),
            'address'      => $this->faker->address(),
            'city'         => $this->faker->city(),
            'service_type' => $this->faker->randomElement(Customer::SERVICE_TYPES),
            'account_type' => $this->faker->randomElement(Customer::ACCOUNT_TYPES),
            'balance'      => $this->faker->randomFloat(2, 0, 100),
            'auto_renewal' => $this->faker->boolean(70),
            'status'       => $this->faker->randomElement(['Active', 'Active', 'Active', 'Inactive']),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'Active']);
    }
}
