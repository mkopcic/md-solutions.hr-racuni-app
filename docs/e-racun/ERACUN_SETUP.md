# FINA e-Račun - Setup Upute

**Datum:** 18. veljače 2026  
**Status:** ✅ Implementirano - SOAP integracija spremna

---

## 📦 Što je implementirano?

Kompletan **SOAP B2B sustav** za FINA e-Račun integraciju s:

### 🏗️ Arhitektura Servisa

```
app/Services/EracunFina/
├── EracunContext.php           # Value object - drži sve e-R račun podatke
├── UblInvoiceGenerator.php     # Generira UBL 2.1 XML format
├── XmlSigner.php               # XMLDSig digitalni potpis
├── CertificateLoader.php       # Učitava .p12 certifikat
├── FinaEracunClient.php        # SOAP komunikacija s FINA-om
└── EracunService.php           # Glavni orchestrator
```

### 🎯 Funkcionalnosti

- ✅ **UBL 2.1 XML generiranje** prema EN 16931 standardu
- ✅ **XMLDSig digitalno potpisivanje** s .p12 certifikatom
- ✅ **SOAP web servis** komunikacija (demo i produkcija)
- ✅ **Slanje računa** prema FINA-i
- ✅ **Provjera statusa** računa
- ✅ **Dohvat ulaznih računa** od drugih firmi
- ✅ **Echo test** za provjeru rada sustava
- ✅ **Dijagnostika** certifikata i konfiguracije

---

## 🚀 Setup Koraci

### 1. Instaliraj robrichards/xmlseclibs

```bash
composer require robrichards/xmlseclibs
```

### 2. Zatraži Demo Certifikat za e-Račun

**VAŽNO:** Certifikat za fiskalizaciju NIJE isti kao certifikat za e-Račun!

**Zahtjev za demo certifikat:**
- Obrazac: https://demo-pki.fina.hr/obrasci/ZahtjevDemoAplikacijski-D20.pdf
- Označi **"e-Račun"** (ne fiskalizacija!)
- Email: eracun.itpodrska@fina.hr

**Preuzimanje certifikata:**
- Portal: https://demo-usercert.fina.hr/cms-user-portal/
- Spremi kao: `storage/certs/eracun_demo.p12`

### 3. Dodaj u .env

```bash
# FINA e-Račun Configuration
ERACUN_ENVIRONMENT=demo

# Demo certifikat
ERACUN_DEMO_CERT_PASSWORD=your_demo_cert_password

# Produkcijski certifikat (kad ga dobiješ)
ERACUN_PROD_CERT_PASSWORD=your_prod_cert_password

# Dobavljač (tvoj obrt/tvrtka)
ERACUN_SUPPLIER_OIB=12345678909
ERACUN_SUPPLIER_NAME="MK Development"
ERACUN_SUPPLIER_ADDRESS="Testna ulica 1"
ERACUN_SUPPLIER_CITY="Zagreb"
ERACUN_SUPPLIER_POSTAL_CODE="10000"
ERACUN_SUPPLIER_IBAN="HR1234567890123456789"

# Logging
ERACUN_LOGGING_ENABLED=true
ERACUN_LOG_XML=true
```

### 4. Kreiraj Direktorij za Certifikate

```bash
mkdir -p storage/certs
# Stavi svoj demo certifikat ovdje:
# storage/certs/eracun_demo.p12
```

### 5. Testiraj Setup

```bash
# Potpuna dijagnostika
php artisan eracun:test diagnostics

# Test echo poruke
php artisan eracun:test echo
```

---

## 🧪 Testiranje

### Dostupne Komande

```bash
# Dijagnostika (certifikat, konfiguracija, SOAP)
php artisan eracun:test diagnostics

# Echo test (provjera SOAP komunikacije)
php artisan eracun:test echo

# Generiraj UBL XML (bez slanja)
php artisan eracun:test ubl --invoice=1

# Pošalji račun
php artisan eracun:test send --invoice=1

# Provjeri status računa
php artisan eracun:test status
```

### Test Output Primjer

```
🚀 FINA e-Račun Test

🔍 Dijagnostika sustava...

📋 KONFIGURACIJA:
┌─────────────────┬──────────────────────────────────────────────┐
│ Parametar       │ Vrijednost                                   │
├─────────────────┼──────────────────────────────────────────────┤
│ Environment     │ demo                                         │
│ WSDL URL        │ https://demo-eracun.fina.hr/.../...?wsdl     │
│ Certifikat path │ /path/to/storage/certs/eracun_demo.p12       │
│ Certifikat pos  │ ✅ DA                                         │
│ Supplier OIB    │ 12345678909                                  │
└─────────────────┴──────────────────────────────────────────────┘

🔐 CERTIFIKAT:
┌─────────────┬────────────────────────┐
│ Parametar   │ Vrijednost             │
├─────────────┼────────────────────────┤
│ Status      │ ✅ VALIDAN              │
│ Subject CN  │ Demo e-Račun Cert      │
│ Issuer CN   │ FINA Demo CA           │
│ Validan od  │ 2026-01-01 00:00:00    │
│ Validan do  │ 2027-01-01 00:00:00    │
└─────────────┴────────────────────────┘

🌐 SOAP KLIJENT:
✅ SOAP klijent radi!

🔒 XML SECURITY:
✅ robrichards/xmlseclibs je instaliran
```

---

## 💻 Korištenje u Kodu

### Slanje Računa

```php
use App\Models\Invoice;
use App\Services\EracunFina\EracunService;

$invoice = Invoice::with(['items', 'customer', 'business'])->find(1);

$service = app(EracunService::class);
$result = $service->sendInvoice($invoice);

if ($result['success']) {
    // Uspješno!
    $status = $result['response']['status']; // ACCEPTED
    $responseData = $result['response'];
} else {
    // Greška
    $error = $result['error'];
}
```

### Provjera Statusa

```php
$service = app(EracunService::class);
$result = $service->getInvoiceStatus('1/2/1/SPO', 2026);

if ($result['success']) {
    // Status dohvaćen
}
```

### Generiranje UBL XML-a

```php
$invoice = Invoice::find(1);
$service = app(EracunService::class);

// Samo generiranje (bez slanja)
$ublXml = $service->generateUblPreview($invoice);

// Potpisivanje (bez slanja)
$signedXml = $service->signXmlPreview($ublXml);
```

---

## 📝 Struktura UBL 2.1 Računa

Generirani UBL XML sadrži:

- ✅ **CustomizationID** - HR specifikacija (urn:mfin.gov.hr:cius-2025:1.0)
- ✅ **ProfileID** - Peppol profil
- ✅ **AccountingSupplierParty** - Dobavljač (tvoji podaci)
- ✅ **AccountingCustomerParty** - Kupac (iz Invoice modela)
- ✅ **PaymentMeans** - Način plaćanja + IBAN
- ✅ **TaxTotal** - PDV sažetak po stopama
- ✅ **LegalMonetaryTotal** - Ukupni iznosi
- ✅ **InvoiceLines** - Stavke s KPD kodovima

---

## 🔧 Produkcija

### 1. Zatraži Produkcijski Certifikat

Nakon uspješnog testiranja na demo okolini:
- Zatraži produkcijski certifikat od FINA-e
- Spremi kao: `storage/certs/eracun_production.p12`

### 2. Ugovori Paket

**Preporučeni paketi:**
- XS: 15 računa/mj - 8,20 EUR/mj
- S-30: 30 računa/mj - 12,20 EUR/mj
- S-55: 55 računa/mj - 20,75 EUR/mj

**Ugovor:** https://www.fina.hr/digitalizacija-poslovanja/e-racun/cjenik-fina-e-racuna

### 3. Promijeni .env

```bash
ERACUN_ENVIRONMENT=production
ERACUN_PROD_CERT_PASSWORD=your_production_password
```

### 4. Restart i Test

```bash
php artisan config:clear
php artisan eracun:test diagnostics
php artisan eracun:test echo
```

---

## 🆘 Troubleshooting

### Certifikat ne postoji

```
❌ Certifikat ne postoji: /path/to/storage/certs/eracun_demo.p12
```

**Rješenje:**
- Provjeri da je certifikat u `storage/certs/eracun_demo.p12`
- Provjeri pisanje imena datoteke (case-sensitive)

### Neispravan password

```
❌ Nije moguće parsirati PKCS#12 certifikat. Provjeri password.
```

**Rješenje:**
- Provjeri `ERACUN_DEMO_CERT_PASSWORD` u `.env`
- Password je osjetljiv na velika/mala slova

### SOAP greška

```
❌ SOAP klijent ne radi
```

**Rješenje:**
- Provjeri internet vezu
- Provjeri WSDL URL dostupnost
- Provjeri firewall postavke

### XMLSecLibs nije instaliran

```
❌ robrichards/xmlseclibs NIJE instaliran!
```

**Rješenje:**
```bash
composer require robrichards/xmlseclibs
```

---

## ⚙️ Konfiguracija

Sve postavke su u `config/eracun.php`:

```php
return [
    'environment' => env('ERACUN_ENVIRONMENT', 'demo'),
    
    'demo' => [
        'wsdl_url' => 'https://demo-eracun.fina.hr/...',
        'cert_path' => storage_path('certs/eracun_demo.p12'),
        'cert_password' => env('ERACUN_DEMO_CERT_PASSWORD'),
    ],
    
    'supplier' => [
        'oib' => env('ERACUN_SUPPLIER_OIB'),
        'name' => env('ERACUN_SUPPLIER_NAME'),
        // ...
    ],
];
```

---

## 📚 Dokumentacija

**FINA:**
- Tehnička specifikacija: https://www.fina.hr/digitalizacija-poslovanja/e-racun/tehnicka-specifikacija
- Integracija: https://www.fina.hr/digitalizacija-poslovanja/e-racun/vodici-za-integraciju
- Podrška: eracun.itpodrska@fina.hr

**EU Standardi:**
- UBL 2.1: https://docs.oasis-open.org/ubl/os-UBL-2.1/
- EN 16931: https://standards.cen.eu/dyn/www/f?p=204:110:0::::FSP_PROJECT:60602

---

## ✅ Sljedeći Koraci

1. ✅ Instaliraj `robrichards/xmlseclibs`
2. ✅ Zatraži demo certifikat za e-Račun
3. ✅ Konfiguriraj `.env`
4. ✅ Testiraj: `php artisan eracun:test diagnostics`
5. ✅ Testiraj: `php artisan eracun:test ubl --invoice=1`
6. ✅ Testiraj slanje: `php artisan eracun:test send --invoice=1`
7. ✅ Ugovori produkcijski paket
8. ✅ Prebaci na production

---

**Autor:** GitHub Copilot  
**Verzija:** 1.0  
**Datum:** 18.02.2026
