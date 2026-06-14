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
            'name' => $this->faker->word() . ' ' . $this->faker->randomElement(['Tablet', 'Syrup', 'Injection', 'Capsule', 'Cream']),
            'description' => $this->faker->sentence(),
            'dosage' => $this->faker->randomElement(['100mg', '250mg', '500mg', '1g', '5ml']),
            'stock' => $this->faker->numberBetween(0, 500),
            'price' => $this->faker->randomFloat(2, 5, 200),
            'category' => $this->faker->randomElement(['Pain Relief', 'Antibiotic', 'Vitamin', 'Antihistamine', 'Antiseptic']),
        ];
    }
}
