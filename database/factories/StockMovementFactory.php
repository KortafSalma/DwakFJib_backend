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
            'type' => $this->faker->randomElement(['IN', 'OUT', 'ADJUSTMENT']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'stock_before' => $this->faker->numberBetween(0, 500),
            'stock_after' => $this->faker->numberBetween(0, 500),
            'reason' => $this->faker->sentence(),
        ];
    }
}
