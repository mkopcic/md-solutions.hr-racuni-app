<?php

namespace Database\Seeders;

use App\Models\EracunLog;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class EracunLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding e-Račun Logs...');

        // Dohvati postojeće račune (Invoice) ili kreiraj nove
        $invoices = Invoice::with('customer')->limit(10)->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('⚠️  Nema postojećih računa. Kreiram dummy račune...');
            // Provjeri postoje li kupci
            $customer = \App\Models\Customer::first();
            if (! $customer) {
                $customer = \App\Models\Customer::factory()->create();
            }

            $invoices = Invoice::factory()->count(10)->create([
                'customer_id' => $customer->id,
            ]);
        }

        // Za svaki račun kreiraj 1-2 e-račun loga
        foreach ($invoices as $invoice) {
            // Uspješno poslani račun
            EracunLog::factory()
                ->for($invoice)
                ->delivered()
                ->create();

            // Random: 30% šanse za failed log
            if (fake()->boolean(30)) {
                $state = fake()->randomElement(['failed', 'rejected']);
                EracunLog::factory()
                    ->for($invoice)
                    ->$state()
                    ->create();
            }
        }

        // Dodaj i nekoliko statusnih logova (bez invoice_id za testiranje)
        // Dohvati random račune za ove logove
        $randomInvoices = Invoice::inRandomOrder()->limit(7)->get();

        EracunLog::factory()
            ->count(3)
            ->pending()
            ->create([
                'invoice_id' => $randomInvoices[0]->id ?? null,
            ]);

        EracunLog::factory()
            ->count(2)
            ->sent()
            ->create([
                'invoice_id' => $randomInvoices[1]->id ?? null,
            ]);

        EracunLog::factory()
            ->count(2)
            ->accepted()
            ->create([
                'invoice_id' => $randomInvoices[2]->id ?? null,
            ]);

        $this->command->info('✅ e-Račun Logs seeded successfully!');
        $this->command->info('   Total logs: '.EracunLog::count());
    }
}
