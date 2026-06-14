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
        'name' => $this->faker->company(),
        'address' => $this->faker->address(),
        'city' => $this->faker->city(),
        'latitude' => $this->faker->latitude(),
        'longitude' => $this->faker->longitude(),
        'phone' => $this->faker->phoneNumber(),
        ];
    }
}
