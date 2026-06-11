<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['ORDER', 'RESERVATION', 'PAYMENT', 'DELIVERY', 'ALERT']),
            'read_at' => fake()->optional(0.7)->dateTime(),
        ];
    }
}
