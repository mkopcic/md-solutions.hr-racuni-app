<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'customer_id' => null, // Postavi u seederu
            'issue_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'note' => $this->faker->sentence(),
            'advance_note' => $this->faker->sentence(),
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'paid_cash' => 0,
            'paid_transfer' => 0,
            'payment_date' => null,
        ];
    }
}
