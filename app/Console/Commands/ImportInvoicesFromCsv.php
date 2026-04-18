<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportInvoicesFromCsv extends Command
{
    protected $signature = 'invoices:import-csv {file : Putanja do CSV fajla (relativno od root-a projekta)} {year : Godina računa (npr. 2025)}';

    protected $description = 'Import računa iz CSV fajla (isti format kao Excel BAZA sheet: stupci A-AT)';

    public function handle(): int
    {
        $file = base_path($this->argument('file'));
        $year = $this->argument('year');

        if (! file_exists($file)) {
            $this->error("❌ Fajl ne postoji: {$file}");

            return 1;
        }

        $this->info("=== CSV Import: {$year} ===");
        $this->info("Fajl: {$file}");

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("❌ Ne mogu otvoriti fajl: {$file}");

            return 1;
        }

        $imported = 0;
        $failed = 0;
        $skipped = 0;
        $rowNum = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNum++;

            // Preskoči prva 3 reda (zaglavlje) - isto kao u Excel importu
            if ($rowNum <= 3) {
                continue;
            }

            // Mapiranje indeksa na slova kolona (0=A, 1=B, ...)
            $invoiceNum = trim($row[0] ?? '');   // A
            $customerName = trim($row[1] ?? ''); // B
            $address = trim($row[2] ?? '');      // C
            $city = trim($row[3] ?? '');         // D
            $oib = trim($row[4] ?? '');          // E

            if (empty($invoiceNum) || (empty($customerName) && empty($oib))) {
                continue;
            }

            $this->line("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("ROW: {$rowNum}");
            $this->info("RAČUN: {$invoiceNum}");
            $this->line("Kupac: {$customerName}");
            $this->line('OIB: '.($oib ?: 'EMPTY'));

            // Provjeri postoji li već račun s tim brojem i godinom - preskoči ako da
            $invoiceNumber = explode('-', $invoiceNum)[0];
            $existingInvoice = Invoice::where('invoice_number', $invoiceNumber)
                ->where('invoice_year', $year)
                ->first();

            if ($existingInvoice) {
                $skipped++;
                $this->warn("⏭ PRESKOČEN - račun {$invoiceNumber}/{$year} već postoji (ID: {$existingInvoice->id})");
                \Log::info('Invoice skipped - already exists', [
                    'invoice_number' => $invoiceNumber,
                    'year' => $year,
                    'existing_id' => $existingInvoice->id,
                    'row' => $rowNum,
                ]);

                continue;
            }

            try {
                // Pronađi ili kreiraj kupca
                $customer = Customer::where('oib', $oib)->first();
                if ($customer) {
                    $this->comment("→ Korisnik postoji (OIB): {$customer->name}");
                } else {
                    $customer = Customer::where('name', $customerName)->first();
                    if ($customer) {
                        $this->comment("→ Korisnik postoji (ime): {$customer->name}");
                    } else {
                        $customer = Customer::create([
                            'name' => $customerName ?: 'Kupac-'.$oib,
                            'address' => $address,
                            'city' => $city,
                            'oib' => $oib,
                        ]);
                        $this->comment("→ NOVI kupac kreiran: {$customer->name}");
                    }
                }

                // Datumi - F=5, G=6, I=8, AT=45
                $issueDate = $this->parseDate($row[5] ?? null) ?? now();
                $deliveryDate = $this->parseDate($row[6] ?? null) ?? $issueDate;
                $dueDate = $this->parseDate($row[8] ?? null);
                $paymentDate = $this->parseDate($row[45] ?? null);

                // Iznosi - AN=39, AQ=42, AR=43
                $totalAmount = $this->parseAmount($row[39] ?? 0);
                $paidCash = $this->parseAmount($row[42] ?? 0);
                $paidTransfer = $this->parseAmount($row[43] ?? 0);

                $this->line("Parsed total: {$totalAmount} | cash: {$paidCash} | transfer: {$paidTransfer}");

                // Status
                $totalPaid = $paidCash + $paidTransfer;
                if ($totalPaid >= $totalAmount && $totalAmount > 0) {
                    $status = 'paid';
                } elseif ($totalPaid > 0) {
                    $status = 'partial';
                } else {
                    $status = 'unpaid';
                }

                // Kreiraj račun
                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_year' => $year,
                    'invoice_type' => 'regular',
                    'issue_date' => $issueDate,
                    'delivery_date' => $deliveryDate,
                    'due_date' => $dueDate,
                    'note' => trim($row[40] ?? ''),         // AO
                    'advance_note' => trim($row[41] ?? ''), // AP
                    'total_amount' => $totalAmount,
                    'subtotal' => $totalAmount,
                    'tax_total' => 0,
                    'paid_cash' => $paidCash,
                    'paid_transfer' => $paidTransfer,
                    'payment_date' => $paymentDate,
                    'payment_method' => 'virman',
                    'status' => $status,
                ]);

                $this->comment("✓ Račun kreiran #{$invoice->id}");

                // Stavke (max 5) - J-O, P-U, V-AA, AB-AG, AH-AM
                // J=9,K=10,L=11,M=12,N=13,O=14
                // P=15,Q=16,R=17,S=18,T=19,U=20
                // V=21,W=22,X=23,Y=24,Z=25,AA=26
                // AB=27,AC=28,AD=29,AE=30,AF=31,AG=32
                // AH=33,AI=34,AJ=35,AK=36,AL=37,AM=38
                $itemCount = 0;
                $itemCols = [
                    [9, 10, 11, 12, 13, 14],
                    [15, 16, 17, 18, 19, 20],
                    [21, 22, 23, 24, 25, 26],
                    [27, 28, 29, 30, 31, 32],
                    [33, 34, 35, 36, 37, 38],
                ];

                foreach ($itemCols as $idx => $cols) {
                    $itemName = trim($row[$cols[0]] ?? '');
                    if (empty($itemName)) {
                        continue;
                    }

                    $unit = trim($row[$cols[1]] ?? 'kom') ?: 'kom';
                    $qty = (int) ($row[$cols[2]] ?? 1);
                    $price = $this->parseAmount($row[$cols[3]] ?? 0);
                    $total = $this->parseAmount($row[$cols[5]] ?? 0);

                    $this->line('  Stavka '.($idx + 1).": {$itemName} | {$qty} {$unit} x {$price}€ = {$total}€");

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => null,
                        'name' => $itemName,
                        'unit' => $unit,
                        'quantity' => $qty,
                        'price' => $price,
                        'discount' => 0,
                        'total' => $total,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                    ]);
                    $itemCount++;
                }

                $this->comment("✓ {$itemCount} stavki kreirano");
                $imported++;

                \Log::info('Invoice imported from CSV', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'year' => $year,
                    'customer' => $customer->name,
                    'total' => $totalAmount,
                    'items' => $itemCount,
                ]);

                $this->info("✅ SUCCESS - IMPORTED #{$imported}");

            } catch (\Exception $e) {
                $failed++;
                $this->error('❌ FAILED: '.$e->getMessage());
                \Log::error('CSV invoice import failed', [
                    'row' => $rowNum,
                    'invoice' => $invoiceNum,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        fclose($handle);

        $this->line("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ {$year}: {$imported} uvezeno, {$skipped} preskočeno (već postoje), {$failed} greška");

        return 0;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('n/j/Y', trim((string) $value));
        } catch (\Exception) {
            return null;
        }
    }

    private function parseAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return (float) str_replace([' ', '€', ','], ['', '', '.'], $value);
        }

        return 0.0;
    }
}
