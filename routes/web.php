<?php

use App\Livewire\Business\BusinessSettings;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Invoices\Create as InvoiceCreate;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Livewire\KPR\Index as KPRIndex;
use App\Livewire\Settings\Appearance;
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

    // Nove rute za fakturu aplikaciju
    Route::get('business/settings', BusinessSettings::class)->name('business.settings');

    // Rute za kupce
    Route::get('customers', CustomersIndex::class)->name('customers.index');

    // Rute za račune
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/create', InvoiceCreate::class)->name('invoices.create');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show');

    // Rute za KPR (knjigu prometa)
    Route::get('kpr', KPRIndex::class)->name('kpr.index');

    // Rute za porezne razrede
    Route::get('tax-brackets', TaxBracketsIndex::class)->name('tax-brackets.index');
    
    // Log Viewer je sada dostupan na /logs kroz konfiguraciju paketa
});

require __DIR__.'/auth.php';
