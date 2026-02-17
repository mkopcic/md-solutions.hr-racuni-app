# QUICK REFERENCE - PDF Redesign Update

**Datum:** 16.02.2026  
**Verzija:** 2.0  
**Status:** ✅ Production Ready

---

## 🎯 Što je Novo?

### 1. Profesionalni PDF Računi
- ✅ Novi dizajn sa plavom temom (#1E40AF)
- ✅ Stiliziran header sa tagline-om
- ✅ Business info box desno
- ✅ 8-kolona tablica sa svim detaljima
- ✅ PDV razrada box (Osnovica/PDV/Ukupno)
- ✅ PDF417 barkod za plaćanje (HUB3)

### 2. Struktuirano Brojanje
- ✅ Format: `broj/mjesec/1/tip` (npr. 1/1/1/SPO)
- ✅ Automatsko generiranje
- ✅ Vlastita sekvenca po tipu računa
- ✅ Tipovi: SPO, AMK, FCZ, SFL, WDR

### 3. PDV Kalkulacija
- ✅ Per stavka: tax_rate, tax_amount
- ✅ Ukupni totali: subtotal, tax_total, total_amount
- ✅ Različite stope: 25%, 13%, 5%, 0%
- ✅ Automatski izračun

### 4. PDF417 Barkod (HUB3)
- ✅ automatski generirani PDF417 barkod (pravokutni 2D barkod)
- ✅ Uključuje: IBAN, iznos, reference, opis
- ✅ Kompatibilno sa svim HR banking app-ovima (PBZ, Zaba, Erste, OTP, itd.)

### 5. Nova Polja
- ✅ invoice_type - Tip računa
- ✅ invoice_number - Redni broj
- ✅ invoice_year - Godina
- ✅ payment_method - Način plaćanja (virman/gotovina/kartica)
- ✅ unit - Jedinica mjere (kom/sat/dan)

---

## 📁 Izmijenjeni Fajlovi

### Migracije (4 nove)
```
database/migrations/2026_02_16_181845_add_invoice_number_fields_to_invoices_table.php
database/migrations/2026_02_16_181854_add_tax_fields_to_invoice_items_table.php
database/migrations/2026_02_16_181901_add_payment_and_tax_fields_to_invoices_table.php
database/migrations/2026_02_16_181904_add_logo_to_business_table.php
```

### Modeli (3 ažurirana)
```
app/Models/Invoice.php - Novi accessor: full_invoice_number
app/Models/InvoiceItem.php - Nova polja za PDV
app/Models/Business.php - Logo path
```

### Controllers (1 ažuriran)
```
app/Http/Controllers/InvoicePdfController.php - QR kod, PDF generation
```

### Livewire Komponente (1 ažurirana)
```
app/Livewire/Invoices/Create.php - Nova logika za brojanje i PDV
```

### Views (3 ažurirane)
```
resources/views/pdf/invoice.blade.php - Kompletan redesign
resources/views/livewire/invoices/create.blade.php - Nova polja
resources/views/livewire/invoices/show.blade.php - Full invoice number display
```

### Packages (1 novi)
```
composer.json - simplesoftwareio/simple-qrcode ^4.2.0
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

**Verzija:** 2.0  
**Ažurirano:** 16.02.2026  
**Status:** ✅ Production Ready  
**Laravel:** 12.x  
**PHP:** 8.3+
