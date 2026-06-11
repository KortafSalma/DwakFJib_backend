<?php

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distributor>
 */
class DistributorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->distributor(),
            'name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
