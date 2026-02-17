<?php

use App\Http\Controllers\InvoicePdfController;
use App\Livewire\ActivityLogs\Index as ActivityLogsIndex;
use App\Livewire\Business\BusinessSettings;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Invoices\Create as InvoiceCreate;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Livewire\KPR\Index as KPRIndex;
use App\Livewire\Services\Index as ServicesIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\EmailSettings;
use App\Livewire\Settings\FaviconSettings;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\TaxBrackets\Index as TaxBracketsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Postojeće rute za postavke
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/favicon', FaviconSettings::class)->name('settings.favicon');
    Route::get('settings/email', EmailSettings::class)->name('settings.email');

    // Nove rute za fakturu aplikaciju
    Route::get('business/settings', BusinessSettings::class)->name('business.settings');

    // Rute za kupce
    Route::get('customers', CustomersIndex::class)->name('customers.index');

    // Rute za usluge
    Route::get('services', ServicesIndex::class)->name('services.index');

    // Rute za račune
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/create', InvoiceCreate::class)->name('invoices.create');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'viewPdf'])->name('invoices.show.pdf');

    // Test barcode route - standalone PNG za testiranje skeniranja
    Route::get('test-barcode/{invoice}', function (\App\Models\Invoice $invoice) {
        $business = \App\Models\Business::first();
        $amountInCents = (int) round($invoice->total_amount * 100);
        $invoiceNumber = $invoice->full_invoice_number ?? $invoice->id;

        $payer = new \Le\PaymentBarcodeGenerator\Party('', '', '');
        $payee = new \Le\PaymentBarcodeGenerator\Party(
            $business->name,
            $business->address,
            $business->location
        );

        $data = new \Le\PaymentBarcodeGenerator\Data(
            payer: $payer,
            payee: $payee,
            iban: $business->iban,
            currency: 'EUR',
            amount: $amountInCents,
            model: 'HR01',
            reference: (string) $invoiceNumber,
            code: 'COST',
            description: "Racun br. {$invoiceNumber}"
        );

        $pdf417 = new \Le\PDF417\PDF417();
        $pdf417->setSecurityLevel(4);
        $pdf417->setColumns(9);

        $renderer = new \Le\PDF417\Renderer\ImageRenderer([
            'format' => 'png',
            'scale' => 4,
            'ratio' => 3,
            'padding' => 30,
        ]);

        $generator = new \Le\PaymentBarcodeGenerator\Generator($pdf417, $renderer);
        $image = $generator->render($data);

        return response($image)->header('Content-Type', 'image/png');
    })->name('test.barcode');

    // Rute za KPR (knjigu prometa)
    Route::get('kpr', KPRIndex::class)->name('kpr.index');

    // Rute za porezne razrede
    Route::get('tax-brackets', TaxBracketsIndex::class)->name('tax-brackets.index');

    // Activity Logs dashboard
    Route::get('activity-logs', ActivityLogsIndex::class)->name('activity-logs.index');

    // Log Viewer je sada dostupan na /logs kroz konfiguraciju paketa
});

require __DIR__.'/auth.php';
