<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pharmacy_id' => Pharmacy::factory(),
            'name' => fake()->word() . ' ' . fake()->randomElement(['Tablet', 'Syrup', 'Injection', 'Capsule', 'Cream']),
            'description' => fake()->sentence(),
            'dosage' => fake()->randomElement(['100mg', '250mg', '500mg', '1g', '5ml']),
            'stock' => fake()->numberBetween(0, 500),
            'price' => fake()->randomFloat(2, 5, 200),
            'category' => fake()->randomElement(['Pain Relief', 'Antibiotic', 'Vitamin', 'Antihistamine', 'Antiseptic']),
        ];
    }
}
