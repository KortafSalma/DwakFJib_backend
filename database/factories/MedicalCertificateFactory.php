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
        $issueDate = fake()->dateTimeBetween('-1 year', '-1 month');

        return [
            'user_id' => User::factory(),
            'file_path' => 'medical-certificates/' . fake()->uuid() . '.pdf',
            'issue_date' => $issueDate,
            'expiry_date' => fake()->dateTimeBetween($issueDate, '+1 year'),
            'status' => fake()->randomElement(['PENDING', 'VERIFIED', 'EXPIRED', 'REJECTED']),
        ];
    }
}
