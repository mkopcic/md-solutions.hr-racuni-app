<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\KprEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KprEntriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Prvo brišemo sve postojeće KPR zapise
        KprEntry::truncate();

        // Dohvaćamo sve račune
        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {            // Određivanje mjeseca iz datuma izdavanja
            $month = $invoice->issue_date instanceof Carbon ? $invoice->issue_date->month : Carbon::now()->month;

            // Kreiranje KPR zapisa za svaki račun
            KprEntry::create([
                'invoice_id' => $invoice->id,
                'month' => $month,
                'amount' => $invoice->total_amount,
            ]);

            echo "Kreiran KPR zapis za račun #{$invoice->id}, mjesec: $month\n";
        }

        echo 'Ukupno kreirano '.KprEntry::count()." KPR zapisa.\n";
    }
}
