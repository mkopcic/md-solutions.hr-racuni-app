<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Invoice;
use App\Services\EracunFina\EracunService;
use Illuminate\Console\Command;

class EracunTest extends Command
{
    protected $signature = 'eracun:test {action=diagnostics} {--invoice= : Invoice ID za testiranje slanja}';

    protected $description = 'FINA e-Račun testiranje i dijagnostika';

    public function handle(): int
    {
        $action = $this->argument('action');

        $this->info('🚀 FINA e-Račun Test');
        $this->newLine();

        return match($action) {
            'diagnostics' => $this->diagnostics(),
            'echo' => $this->testEcho(),
            'send' => $this->sendInvoice(),
            'ubl' => $this->generateUbl(),
            'status' => $this->checkStatus(),
            default => $this->showHelp(),
        };
    }

    protected function diagnostics(): int
    {
        $this->info('🔍 Dijagnostika sustava...');
        $this->newLine();

        $service = app(EracunService::class);
        $diagnostics = $service->diagnostics();

        // Konfiguracija
        $this->line('📋 KONFIGURACIJA:');
        $this->table(
            ['Parametar', 'Vrijednost'],
            [
                ['Environment', $diagnostics['config']['environment']],
                ['WSDL URL', $diagnostics['config']['wsdl_url']],
                ['Certifikat path', $diagnostics['config']['cert_path']],
                ['Certifikat postoji', $diagnostics['config']['cert_exists'] ? '✅ DA' : '❌ NE'],
                ['Supplier OIB', $diagnostics['config']['supplier_oib']],
            ]
        );
        $this->newLine();

        // Certifikat
        $this->line('🔐 CERTIFIKAT:');
        if ($diagnostics['certificate']['valid']) {
            $this->table(
                ['Parametar', 'Vrijednost'],
                [
                    ['Status', '✅ VALIDAN'],
                    ['Subject CN', $diagnostics['certificate']['subject']['CN'] ?? 'N/A'],
                    ['Issuer CN', $diagnostics['certificate']['issuer']['CN'] ?? 'N/A'],
                    ['Validan od', $diagnostics['certificate']['valid_from']],
                    ['Validan do', $diagnostics['certificate']['valid_to']],
                ]
            );
        } else {
            $this->error('❌ Certifikat nije validan: ' . ($diagnostics['certificate']['error'] ?? 'Unknown'));
        }
        $this->newLine();

        // SOAP Klijent
        $this->line('🌐 SOAP KLIJENT:');
        if ($diagnostics['soap_client']['working']) {
            $this->info('✅ SOAP klijent radi!');
            $this->line('Echo response: ' . json_encode($diagnostics['soap_client']['response'], JSON_PRETTY_PRINT));
        } else {
            $this->error('❌ SOAP klijent ne radi: ' . ($diagnostics['soap_client']['error'] ?? 'Unknown'));
        }
        $this->newLine();

        // XMLSecLibs
        $this->line('🔒 XML SECURITY:');
        if ($diagnostics['xmlseclibs']['installed']) {
            $this->info('✅ robrichards/xmlseclibs je instaliran');
        } else {
            $this->error('❌ robrichards/xmlseclibs NIJE instaliran!');
            $this->warn('Pokreni: composer require robrichards/xmlseclibs');
        }

        return self::SUCCESS;
    }

    protected function testEcho(): int
    {
        $this->info('🔔 Test Echo poruke...');
        $this->newLine();

        $service = app(EracunService::class);
        $message = 'Test poruka iz Laravel aplikacije';

        $this->line("Šaljem: {$message}");

        $result = $service->testEcho($message);

        if ($result['success']) {
            $this->info('✅ Echo uspješan!');
            $this->line('Response: ' . json_encode($result['response'], JSON_PRETTY_PRINT));
        } else {
            $this->error('❌ Echo neuspješan!');
            $this->error('Greška: ' . ($result['error'] ?? 'Unknown'));
        }

        return $result['success'] ? self::SUCCESS : self::FAILURE;
    }

    protected function sendInvoice(): int
    {
        $invoiceId = $this->option('invoice');

        if (!$invoiceId) {
            $this->error('Moraš navesti --invoice=ID');
            $this->info('Primjer: php artisan eracun:test send --invoice=1');
            return self::FAILURE;
        }

        $invoice = Invoice::with(['items', 'customer', 'business'])->find($invoiceId);

        if (!$invoice) {
            $this->error("Račun ID {$invoiceId} ne postoji!");
            return self::FAILURE;
        }

        $this->info("📤 Slanje računa: {$invoice->full_invoice_number}");
        $this->line("Kupac: {$invoice->customer->name} (OIB: {$invoice->customer->oib})");
        $this->line("Iznos: {$invoice->total_amount} EUR");
        $this->newLine();

        if (!$this->confirm('Nastaviti sa slanjem?', true)) {
            $this->info('Otkazano.');
            return self::SUCCESS;
        }

        $service = app(EracunService::class);
        $result = $service->sendInvoice($invoice);

        if ($result['success']) {
            $this->info('✅ Račun uspješno poslan!');
            $this->line('Response: ' . json_encode($result['response'], JSON_PRETTY_PRINT));
        } else {
            $this->error('❌ Slanje neuspješno!');
            $this->error('Greška: ' . ($result['error'] ?? 'Unknown'));
        }

        return $result['success'] ? self::SUCCESS : self::FAILURE;
    }

    protected function generateUbl(): int
    {
        $invoiceId = $this->option('invoice');

        if (!$invoiceId) {
            $this->error('Moraš navesti --invoice=ID');
            $this->info('Primjer: php artisan eracun:test ubl --invoice=1');
            return self::FAILURE;
        }

        $invoice = Invoice::with(['items', 'customer', 'business'])->find($invoiceId);

        if (!$invoice) {
            $this->error("Račun ID {$invoiceId} ne postoji!");
            return self::FAILURE;
        }

        $this->info("📄 Generiranje UBL 2.1 XML-a za račun: {$invoice->full_invoice_number}");
        $this->newLine();

        $service = app(EracunService::class);
        $ublXml = $service->generateUblPreview($invoice);

        $this->line('UBL XML:');
        $this->line('----------------------------------------');
        $this->line($ublXml);
        $this->line('----------------------------------------');

        // Spremi u file
        $filename = "ubl_invoice_{$invoice->id}_{$invoice->full_invoice_number}.xml";
        $filename = str_replace(['/', '\\'], '_', $filename);
        $path = storage_path("app/eracun/{$filename}");

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $ublXml);

        $this->newLine();
        $this->info("✅ UBL XML spremljen: {$path}");

        return self::SUCCESS;
    }

    protected function checkStatus(): int
    {
        $invoiceNumber = $this->ask('Broj računa?');
        $year = $this->ask('Godina?', now()->year);

        $this->info("🔍 Provjeravam status računa: {$invoiceNumber} ({$year})");
        $this->newLine();

        $service = app(EracunService::class);
        $result = $service->getInvoiceStatus($invoiceNumber, (int)$year);

        if ($result['success']) {
            $this->info('✅ Status dohvaćen!');
            $this->line('Response: ' . json_encode($result['response'], JSON_PRETTY_PRINT));
        } else {
            $this->error('❌ Greška kod dohvata statusa!');
            $this->error('Greška: ' . ($result['error'] ?? 'Unknown'));
        }

        return $result['success'] ? self::SUCCESS : self::FAILURE;
    }

    protected function showHelp(): int
    {
        $this->info('🚀 FINA e-Račun Testne Komande:');
        $this->newLine();
        $this->line('php artisan eracun:test diagnostics        - Potpuna dijagnostika sustava');
        $this->line('php artisan eracun:test echo              - Test echo poruke');
        $this->line('php artisan eracun:test send --invoice=1  - Pošalji račun');
        $this->line('php artisan eracun:test ubl --invoice=1   - Generiraj UBL XML (bez slanja)');
        $this->line('php artisan eracun:test status            - Provjeri status računa');

        return self::SUCCESS;
    }
}
