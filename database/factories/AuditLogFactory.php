<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted']),
            'auditable_type' => fake()->randomElement(['App\Models\Medication', 'App\Models\Reservation', 'App\Models\Order']),
            'auditable_id' => fake()->numberBetween(1, 100),
            'changes' => ['field' => fake()->word()],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
