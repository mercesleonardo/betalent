<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id'         => \App\Models\Client::factory(),
            'gateway_id'        => null,
            'external_id'       => null,
            'status'            => TransactionStatus::PENDING,
            'amount'            => fake()->numberBetween(1000, 50000),
            'card_last_numbers' => (string) fake()->numberBetween(1000, 9999),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => TransactionStatus::PAID,
            'gateway_id'  => \App\Models\Gateway::factory(),
            'external_id' => 'ext-' . fake()->uuid(),
        ]);
    }
}
