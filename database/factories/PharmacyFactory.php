<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends Factory<Pharmacy>
 */
class PharmacyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'user_id' => \App\Models\User::factory(),
        'name' => fake()->company(),
        'address' => fake()->address(),
        'city' => fake()->city(),
        'latitude' => fake()->latitude(),
        'longitude' => fake()->longitude(),
        'phone' => fake()->phoneNumber(),
        ];
    }
}
