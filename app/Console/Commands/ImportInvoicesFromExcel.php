<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportInvoicesFromExcel extends Command
{
    protected $signature = 'invoices:import-excel';
    protected $description = 'Import invoices from Excel';

    public function handle(): int
    {
        $this->info('Clearing database...');
        \DB::table('invoice_items')->delete();
        \DB::table('invoices')->delete();

        $this->import('docs/izrada-racuna-pausal-2025.xlsx', '2025');
        $this->import('docs/izrada-racuna-pausal-2026-vise-stavaka-04-01-2026.xlsx', '2026');

        return 0;
    }

    private function import($file, $year)
    {
        $this->info("\n=== {$year} ===");

        $spreadsheet = IOFactory::load(base_path($file));
        $sheet = $spreadsheet->getSheetByName('BAZA');
        $rows = $sheet->toArray(null, true, true, true);

        $imported = 0;
        $failed = 0;

        foreach (array_slice($rows, 3) as $rowNum => $row) {
            $invoiceNum = trim($row['A'] ?? '');
            $customerName = trim($row['B'] ?? '');
            $oib = trim($row['E'] ?? '');

            if (empty($invoiceNum) || (empty($customerName) && empty($oib))) {
                continue;
            }

            $this->line("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("ROW: " . ($rowNum + 4));
            $this->info("RAČUN: {$invoiceNum}");
            $this->line("Kupac: {$customerName}");
            $this->line("OIB: " . ($oib ?: 'EMPTY'));
            $this->line("Adresa: " . trim($row['C'] ?? 'EMPTY'));
            $this->line("Grad: " . trim($row['D'] ?? 'EMPTY'));
            $this->line("Datum izdavanja (F): " . ($row['F'] ?? 'EMPTY'));
            $this->line("Datum isporuke (G): " . ($row['G'] ?? 'EMPTY'));
            $this->line("Datum dospijeća (I): " . ($row['I'] ?? 'EMPTY'));
            $this->line("Ukupan iznos (AN): " . ($row['AN'] ?? 'EMPTY'));
            $this->line("Plaćeno gotovina (AQ): " . ($row['AQ'] ?? 'EMPTY'));
            $this->line("Plaćeno virman (AR): " . ($row['AR'] ?? 'EMPTY'));
            $this->line("Datum plaćanja (AT): " . ($row['AT'] ?? 'EMPTY'));

            try {
                // Find or create customer
                $customerExists = Customer::where('oib', $oib)->first();
                if ($customerExists) {
                    $customer = $customerExists;
                    $this->comment("→ Korisnik postoji (OIB): {$customer->name}");
                } else {
                    $customerExists = Customer::where('name', $customerName)->first();
                    if ($customerExists) {
                        $customer = $customerExists;
                        $this->comment("→ Korisnik postoji (ime): {$customer->name}");
                    } else {
                        $customer = Customer::create([
                            'name' => $customerName ?: 'Kupac-' . $oib,
                            'address' => trim($row['C'] ?? ''),
                            'city' => trim($row['D'] ?? ''),
                            'oib' => $oib,
                        ]);
                        $this->comment("→ NOVI kupac kreiran: {$customer->name}");
                    }
                }

                // Parse dates
                $issueDate = $this->parseDate($row['F']) ?? now();
                $deliveryDate = $this->parseDate($row['G']) ?? $issueDate;
                $dueDate = $this->parseDate($row['I']);
                $paymentDate = $this->parseDate($row['AT']);

                $this->line("Parsed issue_date: " . $issueDate->format('Y-m-d'));
                $this->line("Parsed delivery_date: " . $deliveryDate->format('Y-m-d'));
                $this->line("Parsed due_date: " . ($dueDate ? $dueDate->format('Y-m-d') : 'NULL'));

                // Parse amounts
                $totalAmount = $this->parseAmount($row['AN'] ?? 0);
                $paidCash = $this->parseAmount($row['AQ'] ?? 0);
                $paidTransfer = $this->parseAmount($row['AR'] ?? 0);

                $this->line("Parsed total: {$totalAmount}");
                $this->line("Parsed cash: {$paidCash}");
                $this->line("Parsed transfer: {$paidTransfer}");

                // Status
                $totalPaid = $paidCash + $paidTransfer;
                if ($totalPaid >= $totalAmount && $totalAmount > 0) {
                    $status = 'paid';
                } elseif ($totalPaid > 0) {
                    $status = 'partial';
                } else {
                    $status = 'unpaid';
                }
                $this->line("Status: {$status}");

                // Create invoice
                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => explode('-', $invoiceNum)[0],
                    'invoice_year' => $year,
                    'invoice_type' => 'regular',
                    'issue_date' => $issueDate,
                    'delivery_date' => $deliveryDate,
                    'due_date' => $dueDate,
                    'note' => trim($row['AO'] ?? ''),
                    'advance_note' => trim($row['AP'] ?? ''),
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

                // Items (max 5)
                $itemCount = 0;
                foreach ([
                    ['J', 'K', 'L', 'M', 'N', 'O'],
                    ['P', 'Q', 'R', 'S', 'T', 'U'],
                    ['V', 'W', 'X', 'Y', 'Z', 'AA'],
                    ['AB', 'AC', 'AD', 'AE', 'AF', 'AG'],
                    ['AH', 'AI', 'AJ', 'AK', 'AL', 'AM'],
                ] as $idx => $cols) {
                    $itemName = trim($row[$cols[0]] ?? '');
                    if (empty($itemName)) {
                        $this->line("  Stavka " . ($idx + 1) . ": PRAZNA");
                        continue;
                    }

                    $unit = trim($row[$cols[1]] ?? 'kom');
                    $qty = (int)($row[$cols[2]] ?? 1);
                    $price = $this->parseAmount($row[$cols[3]] ?? 0);
                    $total = $this->parseAmount($row[$cols[5]] ?? 0);

                    $this->line("  Stavka " . ($idx + 1) . ": {$itemName} | {$qty} {$unit} x {$price}€ = {$total}€");

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

                // Log to Laravel
                \Log::info("Invoice imported", [
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
                $this->error("❌ FAILED: " . $e->getMessage());
                $this->error("   SQL: " . $e->getTraceAsString());
            }
        }

        $this->line("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ {$year}: {$imported} imported, {$failed} failed");
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('n/j/Y', trim($value));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseAmount($value): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        if (is_string($value)) {
            return (float)str_replace([' ', '€', ','], ['', '', '.'], $value);
        }
        return 0.0;
    }
}
