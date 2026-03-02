# E-Račun Certifikat - Summary

**Datum:** 26. veljača 2026  
**Status:** ✅ CERTIFIKAT USPJEŠNO EKSTRAKTOVAN

---

## 📋 Certifikat Detalji

| Parametar | Vrijednost |
|-----------|------------|
| **Naziv** | MD SOLUTIONS RAČUNI |
| **OIB** | HR86058362621 |
| **Lokacija** | ČEPIN |
| **Skrbnik** | KOPČIĆ MARIJAN |
| **Subjekt** | MD SOLUTIONS VL. MARINA KOPČIĆ |
| **Issuer** | Fina Demo CA 2020 |
| **Validan od** | 26. veljača 2026, 21:27:43 GMT |
| **Validan do** | 31. srpanj 2030, 12:30:18 GMT |
| **SHA1 Fingerprint** | `3E:C2:ED:05:A0:25:4C:6E:33:4B:2B:BE:47:E1:C5:D6:88:68:B8:DC` |
| **Modulus MD5** | `4c92ddd28de69c725362c8f295020138` |

---

## 🔐 Lozinka

```env
ERACUN_DEMO_CERT_PASSWORD="K2wbNnwGuFT4X9"
```

**Referentni broj:** FFAB4A0D69B5D3E798CB

---

## 📁 Ekstraktovani Fajlovi

```
storage/certificates/
├── 86058362621.A.4.p12           # Originalni PKCS#12 (legacy RC2 enkripcija)
├── 86058362621.A.4.pem           # Kombinirani cert+key (za Laravel/Guzzle)
├── 86058362621.A.4-cert.pem      # Samo certifikat
├── 86058362621.A.4-key.pem       # Samo private key
└── fina-demo-ca-root.pem         # FINA Demo CA 2020 root certifikat
```

---

## ⚙️ Konfiguracija

### .env

```env
ERACUN_ENVIRONMENT=demo

# Demo Certificate
ERACUN_DEMO_CERT_PATH="C:/laragon/www/obrt-racuni-laravel-app/storage/certificates/86058362621.A.4.p12"
ERACUN_DEMO_CERT_PASSWORD="K2wbNnwGuFT4X9"

# Supplier Info
ERACUN_SUPPLIER_OIB=86058362621
ERACUN_SUPPLIER_NAME="MD SOLUTIONS VL. MARINA KOPČIĆ"
ERACUN_SUPPLIER_ADDRESS="K. F. ŠEFERA 29"
ERACUN_SUPPLIER_CITY="ČEPIN"
ERACUN_SUPPLIER_POSTAL_CODE="31431"
```

### config/eracun.php

```php
'demo' => [
    'wsdl_url' => 'https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest?wsdl',
    'cert_path' => env('ERACUN_DEMO_CERT_PATH', storage_path('certificates/eracun_demo.p12')),
    'cert_password' => env('ERACUN_DEMO_CERT_PASSWORD'),
],
```

---

## 🔧 Legacy Provider Setup (Windows)

Certifikat koristi **legacy RC2-40 enkripciju** koja zahtijeva OpenSSL legacy provider.

### Environment Varijabla

```powershell
$env:OPENSSL_MODULES = "C:\Program Files\OpenSSL-Win64\bin"
```

**Više detalja:** [docs/OPENSSL_LEGACY_PROVIDER.md](OPENSSL_LEGACY_PROVIDER.md)

---

## 🧪 Testiranje

### 1. Ekstraktovanje certifikata (ako je potrebno ponovno)

```bash
php extract_cert_to_pem.php
```

### 2. Laravel dijagnostika

```bash
php artisan eracun:test diagnostics
```

**Očekivani output:**
- ✅ Certifikat validan
- ✅ SOAP klijent radi
- ✅ xmlseclibs instaliran

### 3. Echo test

```bash
php artisan eracun:test echo
```

### 4. Generiraj UBL XML

```bash
php artisan eracun:test ubl --invoice=1
```

---

## 📚 Dokumentacija

| Dokument | Opis |
|----------|------|
| [OPENSSL_LEGACY_PROVIDER.md](OPENSSL_LEGACY_PROVIDER.md) | Detaljno objašnjenje legacy provider problema i rješenja |
| [e-racun/ERACUN_SETUP.md](e-racun/ERACUN_SETUP.md) | Setup upute za e-Račun integraciju |
| [FINA_E_RACUN_INTEGRACIJA.md](FINA_E_RACUN_INTEGRACIJA.md) | Tehnička specifikacija integracije |

---

## ✅ Validacija

- ✅ Certifikat uspješno ekstraktovan
- ✅ Password validan (`K2wbNnwGuFT4X9`)
- ✅ Cert i key su valjani par (MD5 moduli se poklapaju)
- ✅ SHA1 fingerprint potvrđen
- ✅ Validan do 31. srpanj 2030
- ✅ Legacy provider funkcionira
- ✅ FINA Demo CA root certifikat preuzet i validiran

---

**Sljedeći korak:** Testiranje SOAP komunikacije sa FINA endpointom.

```bash
php artisan eracun:test diagnostics
php artisan eracun:test echo
```
