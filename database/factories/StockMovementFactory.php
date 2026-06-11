<?php

namespace Database\Factories;

use App\Models\StockMovement;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'medication_id' => Medication::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['IN', 'OUT', 'ADJUSTMENT']),
            'quantity' => fake()->numberBetween(1, 100),
            'stock_before' => fake()->numberBetween(0, 500),
            'stock_after' => fake()->numberBetween(0, 500),
            'reason' => fake()->sentence(),
        ];
    }
}
