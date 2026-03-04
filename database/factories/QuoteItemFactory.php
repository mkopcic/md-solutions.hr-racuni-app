<?php

namespace Database\Factories;

use App\Models\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $price = $this->faker->randomFloat(2, 10, 500);
        $discount = $this->faker->randomFloat(2, 0, 50);
        $subtotal = ($quantity * $price) - $discount;
        $taxRate = $this->faker->randomElement([0, 5, 13, 25]);
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        return [
            'quote_id' => null,
            'service_id' => null,
            'name' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'unit' => $this->faker->randomElement(['kom', 'sat', 'dan', 'm2', 'kg']),
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
        ];
    }
}
