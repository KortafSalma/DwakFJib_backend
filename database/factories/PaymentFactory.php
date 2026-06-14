<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'payment_method' => $this->faker->randomElement(['CREDIT_CARD', 'DEBIT_CARD', 'BANK_TRANSFER', 'WALLET']),
            'status' => $this->faker->randomElement(['PENDING', 'COMPLETED', 'FAILED', 'REFUNDED']),
            'transaction_id' => 'TXN-' . strtoupper($this->faker->unique()->randomNumber(10)),
        ];
    }
}
