<?php

namespace Database\Factories;

use App\Enums\IncomingInvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncomingInvoice>
 */
class IncomingInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $taxRate = 25.00;
        $taxTotal = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxTotal;

        $issueDate = fake()->dateTimeBetween('-3 months', 'now');
        $dueDate = (clone $issueDate)->modify('+'.fake()->numberBetween(7, 30).' days');

        return [
            'supplier_oib' => fake()->numerify('###########'),
            'supplier_name' => fake()->company(),
            'supplier_address' => fake()->streetAddress(),
            'supplier_city' => fake()->city(),
            'supplier_postal_code' => fake()->numerify('#####'),
            'supplier_iban' => 'HR'.fake()->numerify('##################'),
            'invoice_number' => fake()->numerify('####-##-####'),
            'fina_invoice_id' => 'FI-'.fake()->uuid(),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'payment_method' => fake()->randomElement(['virman', 'gotovina', 'kartica']),
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total_amount' => $totalAmount,
            'currency' => 'EUR',
            'ubl_xml' => '<?xml version="1.0"?><Invoice>...</Invoice>',
            'status' => IncomingInvoiceStatus::RECEIVED,
            'received_at' => now(),
        ];
    }

    /**
     * Račun koji čeka pregled
     */
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingInvoiceStatus::PENDING_REVIEW,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Odobren račun
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingInvoiceStatus::APPROVED,
            'reviewed_at' => now()->subHours(2),
            'approved_at' => now(),
        ]);
    }

    /**
     * Odbijen račun
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingInvoiceStatus::REJECTED,
            'reviewed_at' => now()->subHours(2),
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Plaćen račun
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingInvoiceStatus::PAID,
            'reviewed_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
            'paid_at' => now(),
        ]);
    }

    /**
     * Arhiviran račun
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingInvoiceStatus::ARCHIVED,
            'reviewed_at' => now()->subDays(10),
            'approved_at' => now()->subDays(9),
            'paid_at' => now()->subDays(8),
            'archived_at' => now(),
        ]);
    }

    /**
     * Račun koji kasni s plaćanjem
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomingInvoiceStatus::APPROVED,
            'issue_date' => now()->subDays(45),
            'due_date' => now()->subDays(15),
            'approved_at' => now()->subDays(40),
        ]);
    }
}
