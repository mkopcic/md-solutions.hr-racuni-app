<?php

namespace Database\Seeders;

use App\Models\IncomingInvoice;
use App\Models\IncomingInvoiceItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class IncomingInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📥 Seeding Incoming Invoices...');

        // Dohvati prvog usera za reviewed_by, approved_by
        $user = User::first();
        if (! $user) {
            $this->command->warn('⚠️  Nema korisnika. Kreiram dummy usera...');
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        // 1. Zaprimljeni računi (RECEIVED) - 5 komada
        $this->command->info('   Creating RECEIVED invoices...');
        IncomingInvoice::factory()
            ->count(5)
            ->has(IncomingInvoiceItem::factory()->count(fake()->numberBetween(2, 5)), 'items')
            ->create();

        // 2. Računi koji čekaju pregled (PENDING_REVIEW) - 3 komada
        $this->command->info('   Creating PENDING_REVIEW invoices...');
        IncomingInvoice::factory()
            ->count(3)
            ->pendingReview()
            ->has(IncomingInvoiceItem::factory()->count(fake()->numberBetween(1, 4)), 'items')
            ->create([
                'reviewed_by' => $user->id,
            ]);

        // 3. Odobreni računi (APPROVED) - 4 komada
        $this->command->info('   Creating APPROVED invoices...');
        IncomingInvoice::factory()
            ->count(4)
            ->approved()
            ->has(IncomingInvoiceItem::factory()->count(fake()->numberBetween(2, 6)), 'items')
            ->create([
                'reviewed_by' => $user->id,
                'approved_by' => $user->id,
            ]);

        // 4. Odbijeni računi (REJECTED) - 2 komada
        $this->command->info('   Creating REJECTED invoices...');
        IncomingInvoice::factory()
            ->count(2)
            ->rejected()
            ->has(IncomingInvoiceItem::factory()->count(fake()->numberBetween(1, 3)), 'items')
            ->create([
                'reviewed_by' => $user->id,
                'rejected_by' => $user->id,
            ]);

        // 5. Plaćeni računi (PAID) - 6 komada
        $this->command->info('   Creating PAID invoices...');
        IncomingInvoice::factory()
            ->count(6)
            ->paid()
            ->has(IncomingInvoiceItem::factory()->count(fake()->numberBetween(2, 5)), 'items')
            ->create([
                'reviewed_by' => $user->id,
                'approved_by' => $user->id,
            ]);

        // 6. Arhivirani računi (ARCHIVED) - 3 komada
        $this->command->info('   Creating ARCHIVED invoices...');
        IncomingInvoice::factory()
            ->count(3)
            ->archived()
            ->has(IncomingInvoiceItem::factory()->count(fake()->numberBetween(1, 4)), 'items')
            ->create([
                'reviewed_by' => $user->id,
                'approved_by' => $user->id,
            ]);

        // 7. Kreiraj 2-3 računa s istog dobavljača (za testiranje filtriranja)
        $this->command->info('   Creating invoices from same supplier...');
        $sameSupplier = [
            'supplier_oib' => '12345678909',
            'supplier_name' => 'Tech Solutions d.o.o.',
            'supplier_address' => 'Ilica 123',
            'supplier_city' => 'Zagreb',
            'supplier_postal_code' => '10000',
        ];

        IncomingInvoice::factory()
            ->count(3)
            ->has(IncomingInvoiceItem::factory()->count(2), 'items')
            ->create($sameSupplier);

        // 8. Kreiraj par računa koji su OVERDUE (istekao rok plaćanja)
        $this->command->info('   Creating OVERDUE invoices...');
        IncomingInvoice::factory()
            ->count(2)
            ->approved()
            ->has(IncomingInvoiceItem::factory()->count(3), 'items')
            ->create([
                'reviewed_by' => $user->id,
                'approved_by' => $user->id,
                'due_date' => now()->subDays(fake()->numberBetween(5, 30)),
            ]);

        $this->command->info('✅ Incoming Invoices seeded successfully!');
        $this->command->info('   Total invoices: '.IncomingInvoice::count());
        $this->command->info('   Total items: '.IncomingInvoiceItem::count());
    }
}
