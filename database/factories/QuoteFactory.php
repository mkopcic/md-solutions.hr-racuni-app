<?php

namespace Database\Factories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $validUntil = $this->faker->dateTimeBetween('now', '+30 days');
        $statuses = ['draft', 'sent', 'accepted', 'rejected'];

        return [
            'customer_id' => null,
            'quote_number' => $this->faker->unique()->numberBetween(1, 999),
            'quote_year' => now()->year,
            'quote_type' => 'R',
            'issue_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
            'valid_until' => $validUntil,
            'note' => $this->faker->optional()->sentence(),
            'internal_notes' => $this->faker->optional()->sentence(),
            'payment_method' => $this->faker->randomElement(['gotovina', 'virman', 'kartica']),
            'total_amount' => $this->faker->randomFloat(2, 100, 5000),
            'subtotal' => $this->faker->randomFloat(2, 80, 4000),
            'tax_total' => $this->faker->randomFloat(2, 20, 1000),
            'status' => $this->faker->randomElement($statuses),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'valid_until' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
