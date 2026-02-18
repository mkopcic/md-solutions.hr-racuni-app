<?php

namespace Database\Factories;

use App\Models\IncomingInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncomingInvoiceItem>
 */
class IncomingInvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $price = fake()->randomFloat(2, 10, 500);
        $total = round($quantity * $price, 2);

        $services = [
            ['name' => 'Konzultantske usluge', 'kpd' => '70220000-7'],
            ['name' => 'IT podrška', 'kpd' => '72000000-5'],
            ['name' => 'Web dizajn', 'kpd' => '72413000-8'],
            ['name' => 'Programiranje', 'kpd' => '72200000-7'],
            ['name' => 'Grafički dizajn', 'kpd' => '79822500-7'],
            ['name' => 'Marketing usluge', 'kpd' => '79340000-9'],
            ['name' => 'Knjigovodstvene usluge', 'kpd' => '79210000-9'],
            ['name' => 'Pravne usluge', 'kpd' => '79100000-5'],
        ];

        $service = fake()->randomElement($services);

        return [
            'incoming_invoice_id' => IncomingInvoice::factory(),
            'name' => $service['name'],
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit' => fake()->randomElement(['kom', 'sat', 'dan', 'm2', 'kg']),
            'price' => $price,
            'total' => $total,
            'tax_rate' => 25.00,
            'kpd_code' => $service['kpd'],
        ];
    }
}
