<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Distributor;
use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'distributor_id' => Distributor::factory(),
            'tracking_number' => 'TRK-' . strtoupper(fake()->unique()->numerify('########')),
            'status' => fake()->randomElement(DeliveryStatus::values()),
            'carrier' => fake()->randomElement(['DHL', 'FedEx', 'UPS', 'Local Courier']),
            'driver_name' => fake()->name(),
            'driver_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'shipping_cost' => fake()->randomFloat(2, 5, 50),
            'notes' => fake()->optional()->sentence(),
            'estimated_delivery' => fake()->optional()->dateTimeBetween('+1 day', '+7 days'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryStatus::PENDING->value,
        ]);
    }

    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryStatus::IN_TRANSIT->value,
            'shipped_at' => now()->subHours(2),
            'in_transit_at' => now()->subHour(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryStatus::DELIVERED->value,
            'shipped_at' => now()->subDays(2),
            'in_transit_at' => now()->subDays(1),
            'delivered_at' => now(),
        ]);
    }
}
