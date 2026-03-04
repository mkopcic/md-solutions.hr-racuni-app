<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->warn('Nema kupaca u bazi. Prvo kreirajte kupce.');

            return;
        }

        // Kreiraj 20 ponuda
        foreach (range(1, 20) as $index) {
            $quote = Quote::factory()->create([
                'customer_id' => $customers->random()->id,
            ]);

            // Kreiraj 2-5 stavki za svaku ponudu
            $itemsCount = rand(2, 5);
            $subtotal = 0;
            $taxTotal = 0;

            for ($i = 0; $i < $itemsCount; $i++) {
                $item = QuoteItem::factory()->create([
                    'quote_id' => $quote->id,
                ]);

                $subtotal += ($item->total - $item->tax_amount);
                $taxTotal += $item->tax_amount;
            }

            // Ažuriraj ukupne iznose ponude
            $quote->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
            ]);
        }

        $this->command->info('Kreirano 20 ponuda sa stavkama.');
    }
}
