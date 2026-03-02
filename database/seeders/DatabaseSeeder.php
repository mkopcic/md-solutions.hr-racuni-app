<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Models\TaxBracket;
use App\Models\User;
use Illuminate\Database\Seeder;

// use kpr entries seeder

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('test'), // Password is 'test'
        ]);

        // Osnovni podatak o obrtu
        Business::factory()->create([
            'name' => 'Moj Obrt',
            'address' => 'Primjer Ulica 1',
            'oib' => '12345678901',
            'iban' => 'HR1234567890123456789',
            'email' => 'info@mojobrt.hr',
            'phone' => '0911234567',
            'location' => 'Zagreb',
            'months_active' => 12,
        ]);

        // Primjer kupca
        $customer = Customer::factory()->create([
            'name' => 'Kupac d.o.o.',
            'address' => 'Kupčeva 2',
            'city' => 'Split',
            'oib' => '98765432109',
        ]);

        // Primjer računa s nekoliko stavki
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 500,
        ]);
        InvoiceItem::factory(3)->create([
            'invoice_id' => $invoice->id,
        ]);

        // Primjer poreznih razreda
        TaxBracket::factory()->create([
            'from_amount' => 0,
            'to_amount' => 40000,
            'yearly_base' => 0,
            'yearly_tax' => 1912.50,
            'monthly_tax' => 159.38,
            'city_tax' => 0,
            'quarterly_amount' => 0,
        ]);

        // Primjer kupaca
        $kupac1 = Customer::factory()->create([
            'name' => 'Web Studio d.o.o.',
            'address' => 'Savska 1',
            'city' => 'Zagreb',
            'oib' => '12312312312',
        ]);
        $kupac2 = Customer::factory()->create([
            'name' => 'IT Solutions j.d.o.o.',
            'address' => 'Vukovarska 10',
            'city' => 'Osijek',
            'oib' => '32132132132',
        ]);

        // Račun za izradu web stranice
        $invoice1 = Invoice::factory()->create([
            'customer_id' => $kupac1->id,
            'issue_date' => now()->subDays(10),
            'delivery_date' => now()->subDays(9),
            'due_date' => now()->addDays(10),
            'note' => 'Izrada web stranice za klijenta',
            'total_amount' => 2500,
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice1->id,
            'name' => 'Izrada web stranice',
            'quantity' => 1,
            'price' => 2000,
            'discount' => 0,
            'total' => 2000,
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice1->id,
            'name' => 'SEO optimizacija',
            'quantity' => 1,
            'price' => 500,
            'discount' => 0,
            'total' => 500,
        ]);

        // Račun za održavanje i razvoj web trgovine
        $invoice2 = Invoice::factory()->create([
            'customer_id' => $kupac2->id,
            'issue_date' => now()->subDays(5),
            'delivery_date' => now()->subDays(4),
            'due_date' => now()->addDays(15),
            'note' => 'Održavanje i razvoj web trgovine',
            'total_amount' => 1800,
            'paid_cash' => 800,
            'paid_transfer' => 1000,
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice2->id,
            'name' => 'Mjesečno održavanje web trgovine',
            'quantity' => 1,
            'price' => 800,
            'discount' => 0,
            'total' => 800,
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice2->id,
            'name' => 'Razvoj nove funkcionalnosti',
            'quantity' => 2,
            'price' => 500,
            'discount' => 0,
            'total' => 1000,
        ]);

        // Dodajemo više računa za razdoblje od prethodnih 6 mjeseci
        for ($i = 1; $i <= 3; $i++) {
            $invoiceDate = now()->subMonths($i);
            $invoice = Invoice::factory()->create([
                'customer_id' => $kupac1->id,
                'issue_date' => $invoiceDate,
                'delivery_date' => $invoiceDate,
                'due_date' => $invoiceDate->copy()->addDays(15),
                'note' => 'Mjesečno održavanje - '.$invoiceDate->format('m/Y'),
                'total_amount' => 800,
                'paid_cash' => 800,
            ]);

            InvoiceItem::factory()->create([
                'invoice_id' => $invoice->id,
                'name' => 'Mjesečno održavanje web stranice',
                'quantity' => 1,
                'price' => 800,
                'discount' => 0,
                'total' => 800,
            ]);
        }

        // Dodatni računi za kupca 2
        for ($i = 1; $i <= 3; $i++) {
            $invoiceDate = now()->subMonths($i)->subDays(mt_rand(1, 10));
            $invoice = Invoice::factory()->create([
                'customer_id' => $kupac2->id,
                'issue_date' => $invoiceDate,
                'delivery_date' => $invoiceDate,
                'due_date' => $invoiceDate->copy()->addDays(15),
                'note' => 'Razvoj funkcionalnosti - '.$invoiceDate->format('m/Y'),
                'total_amount' => 1200,
                'paid_transfer' => 1200,
            ]);

            InvoiceItem::factory()->create([
                'invoice_id' => $invoice->id,
                'name' => 'Razvoj novih funkcionalnosti',
                'quantity' => 2,
                'price' => 600,
                'discount' => 0,
                'total' => 1200,
            ]);
        }

        // Dodatni računi za različite datume u prijašnjim mjesecima
        $customers = [$kupac1, $kupac2, $customer];
        for ($i = 1; $i <= 5; $i++) {
            $randomCustomer = $customers[array_rand($customers)];
            $invoiceDate = now()->subMonths(mt_rand(1, 5))->subDays(mt_rand(1, 28));
            $amount = mt_rand(5, 30) * 100; // Između 500 i 3000

            $invoice = Invoice::factory()->create([
                'customer_id' => $randomCustomer->id,
                'issue_date' => $invoiceDate,
                'delivery_date' => $invoiceDate,
                'due_date' => $invoiceDate->copy()->addDays(15),
                'note' => 'Usluge za '.$invoiceDate->format('m/Y'),
                'total_amount' => $amount,
                'paid_cash' => $i % 2 === 0 ? $amount : 0,
                'paid_transfer' => $i % 2 === 0 ? 0 : $amount,
            ]);

            $itemCount = mt_rand(1, 3);
            $itemAmount = $amount / $itemCount;

            for ($j = 1; $j <= $itemCount; $j++) {
                InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                    'name' => 'Usluga #'.$j,
                    'quantity' => 1,
                    'price' => $itemAmount,
                    'discount' => 0,
                    'total' => $itemAmount,
                ]);
            }
        }

        // Kreiraj test services
        Service::factory()->create([
            'name' => 'Izrada web stranice',
            'description' => 'Kompletna izrada responzivne web stranice',
            'price' => 2500.00,
            'unit' => 'kom',
            'active' => true,
        ]);

        Service::factory()->create([
            'name' => 'SEO optimizacija',
            'description' => 'Optimizacija stranice za tražilice',
            'price' => 500.00,
            'unit' => 'sat',
            'active' => true,
        ]);

        Service::factory()->create([
            'name' => 'Održavanje aplikacije',
            'description' => 'Mjesečno održavanje i podrška',
            'price' => 300.00,
            'unit' => 'mjesec',
            'active' => true,
        ]);

        // Seederi iz Excel podataka
        $this->call([
            BusinessSeeder::class,
            CustomersSeeder::class,
            TaxBracketsSeeder::class,
            ServicesSeeder::class,
        ]);

        // Pokreni KPR entries seeder
        $this->call(KprEntriesSeeder::class);

        // e-Račun seederi
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('   SEEDING e-RAČUN DATA');
        $this->command->info('═══════════════════════════════════════════');
        $this->call([
            IncomingInvoiceSeeder::class,
            EracunLogSeeder::class,
        ]);
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('   ✅ e-RAČUN DATA SEEDED');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');
    }
}
