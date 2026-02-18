# QUICK REFERENCE - e-Račun & UI Update

**Datum:** 18.02.2026  
**Verzija:** 2.2  
**Status:** ✅ Production Ready

---

## 🎯 Što je Novo?

### 1. e-Račun FINA Integracija ⭐
- ✅ IncomingInvoice model za dolazne račune
- ✅ EracunService za FINA API komunikaciju (UBL 2.1)
- ✅ XML potpis za autentifikaciju (XMLDSig)
- ✅ 4 Artisan commanda (test, sync, import, report)
- ✅ EracunLog model za praćenje komunikacije
- ✅ Business model proširen (in_vat_system, business_space_label, cash_register_label)
- ✅ Migracije za incoming_invoices, incoming_invoice_items, eracun_logs

### 2. Invoice Tablica UI Poboljšanja
- ✅ Dodane kolone: ID, Broj računa (123/2024), Tip
- ✅ Tip lokaliziran na hrvatski: R→Račun, RA→Avansni, P→Predračun
- ✅ Neutral gray badge umjesto plavog
- ✅ Ispravljen filter reset (year, month, customer_id uključeni)

### 3. Services Tablica Text Overflow Fix
- ✅ Naziv limitiran na 40 znakova s tooltip-om
- ✅ Opis limitiran na 60 znakova s tooltip-om
- ✅ max-w-xs i max-w-md CSS klase za konzistentnu širinu

### 4. Activity Logs Clear All
- ✅ clearAllLogs() metoda - briše Spatie, Laravel, Debugbar logove
- ✅ Crveni danger button s potvrdom
- ✅ Header layout ispravljen (div struktura)

### 5. Profesionalni PDF Računi (v2.0)
- ✅ Novi dizajn sa plavom temom (#1E40AF)
- ✅ Stiliziran header sa tagline-om
- ✅ Business info box desno
- ✅ 8-kolona tablica sa svim detaljima
- ✅ PDV razrada box (Osnovica/PDV/Ukupno)
- ✅ PDF417 barkod za plaćanje (HUB3)

### 6. Struktuirano Brojanje (v2.0)
- ✅ Format: `broj/mjesec/1/tip` (npr. 1/1/1/SPO)
- ✅ Automatsko generiranje
- ✅ Vlastita sekvenca po tipu računa
- ✅ Tipovi: SPO, AMK, FCZ, SFL, WDR

---

## 📦 e-Račun Infrastruktura

### Novi Paketi
```json
"robrichards/xmlseclibs": "^3.1"
```

### Artisan Komande
```bash
# Test FINA konekcije
php artisan eracun:test

# Sinkroniziraj statuse
php artisan invoices:sync-status

# Uvoz iz Excel-a
php artisan invoices:import path/to/file.xlsx

# Pošalji izvještaj
php artisan invoices:send-report --email=admin@example.com
```

### Modeli (3 nova)
- `app/Models/IncomingInvoice.php` - Dolazni računi
- `app/Models/IncomingInvoiceItem.php` - Stavke dolaznih računa
- `app/Models/EracunLog.php` - e-Račun komunikacija log

### Enums (3 nova)
- `app/Enums/EracunStatus.php` - Statusi e-računa
- `app/Enums/FinaStatus.php` - FINA API statusi
- `app/Enums/IncomingInvoiceStatus.php` - Statusi dolaznih računa

### Services
- `app/Services/EracunService.php` - FINA API integracija

### Config
- `config/eracun.php` - e-Račun konfiguracija

### Migracije (4 nove)
```
database/migrations/2026_02_18_082436_create_incoming_invoices_table.php
database/migrations/2026_02_18_082437_create_incoming_invoice_items_table.php
database/migrations/2026_02_18_082438_create_eracun_logs_table.php
database/migrations/2026_02_18_103347_add_eracun_fields_to_businesses_table.php
```

---

## 📁 Izmijenjeni Fajlovi

### Migracije (8 novih)
```
# PDF Redesign (v2.0)
database/migrations/2026_02_16_181845_add_invoice_number_fields_to_invoices_table.php
database/migrations/2026_02_16_181854_add_tax_fields_to_invoice_items_table.php
database/migrations/2026_02_16_181901_add_payment_and_tax_fields_to_invoices_table.php
database/migrations/2026_02_16_181904_add_logo_to_business_table.php

# e-Račun (v2.2)
database/migrations/2026_02_18_082436_create_incoming_invoices_table.php
database/migrations/2026_02_18_082437_create_incoming_invoice_items_table.php
database/migrations/2026_02_18_082438_create_eracun_logs_table.php
database/migrations/2026_02_18_103347_add_eracun_fields_to_businesses_table.php
```

### Modeli (6 ažuriranih/novih)
```
app/Models/Invoice.php - Novi accessor: full_invoice_number, status sync
app/Models/InvoiceItem.php - Nova polja za PDV
app/Models/Business.php - Logo path, e-Račun polja (in_vat_system, business_space_label, cash_register_label)
app/Models/IncomingInvoice.php - ⭐ Novi (e-Račun)
app/Models/IncomingInvoiceItem.php - ⭐ Novi (e-Račun)
app/Models/EracunLog.php - ⭐ Novi (e-Račun)
```

### Livewire Komponente (5 ažuriranih)
```
app/Livewire/Invoices/Create.php - Nova logika za brojanje i PDV
app/Livewire/Invoices/Index.php - Filter reset fix, nova polja
app/Livewire/Invoices/Show.php - Full invoice number display
app/Livewire/ActivityLogs/Index.php - clearAllLogs() metoda ⭐
app/Livewire/Business/BusinessSettings.php - e-Račun polja ⭐
```

### Views (5 ažuriranih)
```
resources/views/pdf/invoice.blade.php - Kompletan redesign
resources/views/livewire/invoices/create.blade.php - Nova polja
resources/views/livewire/invoices/show.blade.php - Full invoice number display
resources/views/livewire/invoices/index.blade.php - ID, broj računa, tip kolone ⭐
resources/views/livewire/services/index.blade.php - Text truncation ⭐
resources/views/livewire/activity-logs/index.blade.php - Clear button, header fix ⭐
resources/views/livewire/business/business-settings.blade.php - e-Račun polja ⭐
```

### Services (1 novi)
```
app/Services/EracunService.php - FINA API integracija, UBL 2.1, XMLDSig
```

### Commands (4 nova)
```
app/Console/Commands/EracunTest.php - Test FINA konekcije
app/Console/Commands/ImportInvoicesFromExcel.php - Import računa
app/Console/Commands/SendImportReport.php - Email izvještaji
app/Console/Commands/SyncInvoiceStatus.php - Status sinkronizacija
```

### Config (2 nova/ažurirana)
```
config/eracun.php - ⭐ Novi (e-Račun konfiguracija)
config/mail.php - Email settings opcije
```

### Packages (2 nova)
```
composer.json:
- simplesoftwareio/simple-qrcode ^4.2.0 (v2.0)
- robrichards/xmlseclibs ^3.1 (v2.2) ⭐
```

---

## 🎨 UI Izmjene (v2.2)

### Invoice Tablica
**Nove kolone:**
- **ID** - Database ID računa
- **Broj računa** - Format: `123/2024` (invoice_number/invoice_year)
- **Tip** - Lokalizirano: "Račun" (R), "Avansni" (RA), "Predračun" (P)

**Styling:**
- Tipovi prikazani kao neutral gray badge (`bg-zinc-100`)
- Colspan ažuriran na 10 za prazno stanje

**Filter Reset Fix:**
```php
// Prije - nepotpuno
reset(['search', 'status', 'paymentMethod', 'dateFrom', 'dateTo']);

// Sada - kompletno
reset(['search', 'status', 'paymentMethod', 'dateFrom', 'dateTo', 'year', 'month', 'customer_id']);
$this->resetPage();
```

### Services Tablica
**Text Overflow Fix:**
```blade
<!-- Naziv - 40 znakova -->
<div class="max-w-xs" title="{{ $service->name }}">
    {{ Str::limit($service->name, 40) }}
</div>

<!-- Opis - 60 znakova -->
<div class="max-w-md" title="{{ $service->description }}">
    {{ Str::limit($service->description, 60) }}
</div>
```

**Benefiti:**
- Konzistentna širina tablice
- Tooltip sa punim tekstom na hover
- Elipsa (...) za skraćeni tekst

### Activity Logs Clear All
**Nova funkcionalnost:**
```php
public function clearAllLogs()
{
    try {
        // 1. Spatie Activity Logs
        Activity::query()->delete();
        
        // 2. Laravel Application Logs
        File::cleanDirectory(storage_path('logs'));
        
        // 3. Browser/Debugbar Logs
        File::cleanDirectory(storage_path('debugbar'));
        
        session()->flash('success', '✅ Svi logovi su uspješno obrisani.');
    } catch (\Exception $e) {
        session()->flash('error', '❌ Greška pri brisanju logova: ' . $e->getMessage());
    }
}
```

**UI:**
- Crveni danger button: "Obriši sve logove"
- Confirmation dialog: `wire:confirm="Jeste li sigurni? Ova akcija je nepovratna."`
- Header layout fix - consistent div struktura

### Business Settings e-Račun Polja
**Nova polja:**
```php
$table->boolean('in_vat_system')->default(false);
$table->string('business_space_label')->nullable();
$table->string('cash_register_label')->nullable();
```

**UI:**
- Checkbox: "U PDV sustavu"
- Input: "Oznaka poslovnog prostora" (POS1, OFFICE, itd.)
- Input: "Oznaka blagajne" (KASA1, BLAGAJNA-01, itd.)

---

## ⚡ e-Račun Quick Start

### 1. Setup FINA Credentials (.env)
```env
FINA_API_URL=https://era-test.fina.hr/api/v1
FINA_USERNAME=your_username
FINA_PASSWORD=your_password
FINA_CERTIFICATE_PATH=storage/certs/cert.p12
FINA_CERTIFICATE_PASSWORD=cert_pass
FINA_VAT_ID=12345678901
FINA_GLN=3850000000000
```

### 2. Business Settings
1. Idi na Settings → Business
2. Popuni:
   - ✅ U PDV sustavu (checkbox)
   - ✅ Oznaka poslovnog prostora (npr. "POS1")
   - ✅ Oznaka blagajne (npr. "KASA1")
3. Save

### 3. Test Konekciju
```bash
php artisan eracun:test
```

### 4. Sinkroniziraj Statuse
```bash
php artisan invoices:sync-status
```

---

## 🚀 Korištenje

### Kreiranje Računa

1. Idi na `/invoices/create`
2. Odaberi **Tip računa** (default: SPO)
3. Broj računa se **automatski generira**
4. Odaberi **Način plaćanja**
5. Popuni customer i datume
6. Dodaj stavke:
   - Opis
   - **Jedinica mjere** (kom/sat/dan)
   - Količina, cijena
   - **PDV stopa** (iz dropdown-a)
   - Popust (opcionalno)
7. Provjeri **totale** (Osnovica, PDV, Ukupno)
8. Spremi

### Generiranje PDF-a

1. Otvori račun
2. Klikni **"Prikaži PDF"** ili **"Preuzmi PDF"**
3. PDF sadrži sve nove elemente uključujući **QR kod**

### Plaćanje QR Kodom

1. Kupac otvori banking app
2. Skeniraj QR sa računa
3. Svi podaci automatski popunjeni
4. Potvrdi plaćanje

---

## 🔢 Format Broja Računa

### Sintaksa
```
{invoice_number}/{month}/1/{invoice_type}
```

### Primjeri
```
1/1/1/SPO    - Prvi SPO račun u siječnju
2/1/1/SPO    - Drugi SPO račun u siječnju
1/2/1/SPO    - Prvi SPO račun u veljači (nastavlja sekvencu)
1/1/1/AMK    - Prvi AMK račun u siječnju (vlastita sekvenca)
```

### Pravila
- Svaki tip ima vlastitu sekvencu
- Redni broj se nastavlja kroz godinu
- Resetira se svake nove godine
- Automatski generiranje - **ne može se mijenjati ručno**

---

## 💳 Tipovi Računa

| Kod | Opis | Korištenje |
|-----|------|-----------|
| **SPO** | Standard Payment Order | Najčešće (default) |
| **AMK** | Advanced Model Kategorija | Napredni modeli |
| **FCZ** | Foreign Currency Zone | Strane valute |
| **SFL** | Special Fiscal Label | Specijala fiskalne oznake |
| **WDR** | Withdrawal | Povlačenja |

---

## 💹 PDV Stope

| Stopa | Primjena |
|-------|----------|
| **25%** | Opća stopa (default) |
| **13%** | Snižena stopa |
| **5%** | Posebno snižena |
| **0%** | Oslobođeno PDV-a |

---

## 📐 Jedinice Mjere

| Kod | Naziv | Korištenje |
|-----|-------|-----------|
| **kom** | komada | Proizvodi (default) |
| **sat** | sati | Usluge po satu |
| **dan** | dani | Usluge po danu |

---

## 🏦 PDF417 Barkod (HUB3)

### Format
- **Tip:** PDF417 2D barkod (pravokutni, crno-bijeli)
- **Biblioteka:** bigfish/pdf417 ^0.3.0
- **Standard:** HUB3 (Hrvatski Univerzalni Barkod)

### Što Sadrži?
```
HRVHUB30
EUR
{amount}
{business_name}
{business_address}
{business_location}
{business_iban}
HR01 (model plaćanja)
{invoice_number} (poziv na broj)
COST (šifra namjene)
Racun br. {invoice_number}
```

### Podržane Banke
✅ Sve hrvatske banke (PBZ, Zaba, Erste George, OTP, Addiko, Revolut, itd.)
✅ Skeniranje preko banking aplikacija
✅ Automatsko popunjavanje podataka za plaćanje

---

## 🧪 Testiranje

### Provjera Tax Brackets
```bash
php artisan tinker --execute="echo App\Models\TaxBracket::count();"
# Očekivano: 2 ili više
```

### Testni Scenario
1. Kreiraj račun tip **SPO**
2. Dodaj 2 stavke sa različitim PDV stopama
3. Provjeri da li se totali ispravno računaju
4. Generiraj PDF
5. Provjeri PDF dizajn i QR kod
6. Test skeniranja QR koda (banking app)

---

## 🔄 Rollback

Ako je potreban rollback:

```bash
# Rollback zadnje 4 migracije
php artisan migrate:rollback --step=4
```

---

## 📚 Dokumentacija

| Dokument | Opis |
|----------|------|
| **[USER_GUIDE.md](USER_GUIDE.md)** | Korisnički vodič |
| **[CHANGELOG_PDF_REDESIGN.md](CHANGELOG_PDF_REDESIGN.md)** | Tehnički detalji |
| **[INSTALLATION.md](INSTALLATION.md)** | Setup i konfiguracija |

---

## ✅ Checklist

- [x] Migracije kreirane i izvršene
- [x] Modeli ažurirani
- [x] QR package instaliran
- [x] PDF template redesigniran
- [x] Create komponenta ažurirana
- [x] Create view ažuiran
- [x] Show view ažuriran
- [x] Code formatting (Pint)
- [x] Dokumentacija kreirana
- [x] Tax brackets postoje u bazi

---

## 🐛 Troubleshooting

### Problem: Tax brackets ne postoje
```bash
php artisan db:seed --class=TaxBracketsSeeder
```

### Problem: QR kod se ne prikazuje
- Provjeri da IBAN u Business settings postoji
- Provjeri da iznos računa je > 0
- Provjeri SimpleSoftwareIO package instaliran

### Problem: PDF ne generira
- Provjeri `storage/logs/laravel.log`
- Provjeri da Business postoji u bazi
- Provjeri UTF-8 encoding

---

## 📞 Support

Za pitanja:
1. Provjeri dokumentaciju
2. Provjeri logove (`storage/logs/`)
3. Kontaktiraj developera

---

**Verzija:** 2.2  
**Ažurirano:** 18.02.2026  
**Status:** ✅ Production Ready  
**Laravel:** 12.x  
**PHP:** 8.3+
