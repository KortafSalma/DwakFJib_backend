<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pharmacy_id' => Pharmacy::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->paragraph(),
            'is_verified' => fake()->boolean(),
        ];
    }
}
