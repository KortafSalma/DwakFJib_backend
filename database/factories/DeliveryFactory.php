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
            'tracking_number' => 'TRK-' . strtoupper($this->faker->unique()->numerify('########')),
            'status' => $this->faker->randomElement(DeliveryStatus::values()),
            'carrier' => $this->faker->randomElement(['DHL', 'FedEx', 'UPS', 'Local Courier']),
            'driver_name' => $this->faker->name(),
            'driver_phone' => $this->faker->phoneNumber(),
            'shipping_address' => $this->faker->address(),
            'shipping_cost' => $this->faker->randomFloat(2, 5, 50),
            'notes' => $this->faker->optional()->sentence(),
            'estimated_delivery' => $this->faker->optional()->dateTimeBetween('+1 day', '+7 days'),
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
