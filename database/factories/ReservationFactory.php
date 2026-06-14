<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $medication = Medication::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'user_id' => User::factory(),
            'pharmacy_id' => $medication->pharmacy_id,
            'medication_id' => $medication->id,
            'quantity' => $quantity,
            'deposit_amount' => $medication->price * $quantity * 0.5,
            'status' => $this->faker->randomElement(['PENDING', 'PAID', 'CANCELLED', 'COMPLETED']),
        ];
    }
}
