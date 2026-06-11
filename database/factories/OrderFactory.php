<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pharmacy_id' => Pharmacy::factory(),
            'distributor_id' => Distributor::factory(),
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->randomNumber(8)),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->randomElement(['PENDING', 'CONFIRMED', 'SHIPPED', 'DELIVERED', 'CANCELLED']),
            'delivery_date' => fake()->optional()->dateTimeBetween('+1 week', '+1 month'),
        ];
    }
}
