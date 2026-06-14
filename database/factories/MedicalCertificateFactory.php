<?php

namespace Database\Factories;

use App\Models\MedicalCertificate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalCertificate>
 */
class MedicalCertificateFactory extends Factory
{
    public function definition(): array
    {
        $issueDate = $this->faker->dateTimeBetween('-1 year', '-1 month');

        return [
            'user_id' => User::factory(),
            'file_path' => 'medical-certificates/' . $this->faker->uuid() . '.pdf',
            'issue_date' => $issueDate,
            'expiry_date' => $this->faker->dateTimeBetween($issueDate, '+1 year'),
            'status' => $this->faker->randomElement(['PENDING', 'VERIFIED', 'EXPIRED', 'REJECTED']),
        ];
    }
}
