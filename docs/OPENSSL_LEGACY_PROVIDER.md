# OpenSSL Legacy Provider - Windows Setup

**Datum:** 26. veljača 2026  
**Problem:** FINA e-Račun certifikati koriste legacy RC2/3DES enkripciju  
**Rješenje:** Omogućiti OpenSSL legacy provider

---

## 🔴 Problem

FINA demo certifikati (`.p12` fajlovi) koriste **stare algoritme enkripcije**:
- RC2-40-CBC
- 3DES (Triple DES)
- PBE-SHA1-RC2-40

**OpenSSL 3.x** je uklonio podršku za ove algoritme iz sigurnosnih razloga i premjestio ih u **legacy provider**.

### Greška bez legacy providera:

```
error:0308010C:digital envelope routines::unsupported
```

ili u PHP-u:

```php
openssl_pkcs12_read($p12Content, $certs, $password);
// Vraća: error:0308010C:digital envelope routines::unsupported
```

---

## ✅ Rješenje za Windows

### Korak 1: Provjeri OpenSSL instalaciju

```powershell
& "C:\Program Files\OpenSSL-Win64\bin\openssl.exe" version
# Output: OpenSSL 3.6.1 27 Jan 2026
```

### Korak 2: Provjeri postojanje legacy provider-a

```powershell
Get-ChildItem "C:\Program Files\OpenSSL-Win64" -Recurse -Filter "legacy.dll"
```

**Ako ne postoji**: Instaliraj **punu verziju OpenSSL-a** (ne "Light"):
- Download: https://slproweb.com/products/Win32OpenSSL.html
- Odaberi: **Win64 OpenSSL v3.x.x** (ne Light verziju!)

### Korak 3: Postavi environment varijablu

OpenSSL traži providere u `OPENSSL_MODULES` lokaciji.

#### Opcija A: U PowerShell sesiji (privremeno)

```powershell
$env:OPENSSL_MODULES = "C:\Program Files\OpenSSL-Win64\bin"
```

#### Opcija B: Systemwide (trajno)

1. `Win + R` → `sysdm.cpl` → **Advanced** → **Environment Variables**
2. U **System variables** dodaj:
   - Ime: `OPENSSL_MODULES`
   - Vrijednost: `C:\Program Files\OpenSSL-Win64\bin`
3. Restart terminala/aplikacije

### Korak 4: Koristi legacy provider

```powershell
& "C:\Program Files\OpenSSL-Win64\bin\openssl.exe" pkcs12 `
  -provider legacy `
  -provider default `
  -in "storage\certificates\86058362621.A.4.p12" `
  -out "storage\certificates\86058362621.A.4.pem" `
  -nodes `
  -passin "pass:K2wbNnwGuFT4X9"
```

**Ključni parametri:**
- `-provider legacy` - učitaj legacy algoritme (RC2, 3DES)
- `-provider default` - učitaj standardne algoritme
- Redoslijed je važan: **legacy PRIJE default**

---

## 🔍 Kako radi?

### OpenSSL 3.x Arhitektura

OpenSSL 3.x koristi **modularni provider sustav**:

```
┌─────────────────────────────────────┐
│   OpenSSL 3.x Core Engine           │
├─────────────────────────────────────┤
│                                     │
│  ┌──────────────┐  ┌──────────────┐│
│  │   default    │  │    legacy    ││
│  │  provider    │  │   provider   ││
│  │              │  │              ││
│  │ AES-256      │  │ RC2-40       ││
│  │ RSA-2048+    │  │ 3DES         ││
│  │ SHA-256+     │  │ MD5          ││
│  └──────────────┘  └──────────────┘│
└─────────────────────────────────────┘
```

### Certifikat Struktura (.p12)

```
PKCS#12 Bundle
├── Private Key (RC2-40 encrypted) ← LEGACY!
├── Certificate (X.509)
└── CA Chain (optional)
```

- **Private key** je enkriptiran sa RC2-40
- Za dekriptovanje potreban je **legacy provider**
- Nakon dekriptovanja, key i cert se spremaju u PEM format

---

## 📝 PHP Integration

PHP interno koristi OpenSSL library. Ako legacy provider nije dostupan, PHP funkcije neće raditi:

```php
// ❌ NE RADI bez legacy providera (u PHP-u)
$result = openssl_pkcs12_read($p12Content, $certs, $password);
// Error: digital envelope routines::unsupported
```

**Rješenje:** Koristimo OpenSSL CLI umjesto PHP funkcija.

### extract_cert_to_pem.php

```php
// Postavi environment varijablu u PHP-u
putenv('OPENSSL_MODULES=C:\Program Files\OpenSSL-Win64\bin');

// Pozovi OpenSSL CLI sa legacy providerom
$cmd = sprintf(
    '"%s" pkcs12 -provider legacy -provider default -in "%s" -out "%s" -nodes -passin "pass:%s"',
    $opensslPath,
    $p12Path,
    $outputPath,
    $password
);
exec($cmd, $output, $returnCode);
```

---

## 🧪 Testiranje

### 1. Test ekstraktovanja certifikata

```bash
php extract_cert_to_pem.php
```

**Očekivani output:**
```
✅ OpenSSL pronađen
✅ Legacy provider pronađen
📦 Ekstraktujem kombinirani PEM...
✅ Kombinirani PEM: storage/certificates/86058362621.A.4.pem
📦 Ekstraktujem certifikat...
✅ Certifikat: storage/certificates/86058362621.A.4-cert.pem
📦 Ekstraktujem private key...
✅ Private key: storage/certificates/86058362621.A.4-key.pem
```

### 2. Validacija cert/key para

```powershell
# Cert modulus
& "C:\Program Files\OpenSSL-Win64\bin\openssl.exe" x509 `
  -in "storage\certificates\86058362621.A.4-cert.pem" `
  -noout -modulus | openssl md5

# Key modulus
& "C:\Program Files\OpenSSL-Win64\bin\openssl.exe" rsa `
  -in "storage\certificates\86058362621.A.4-key.pem" `
  -noout -modulus | openssl md5
```

**MD5 hashevi MORAJU biti identični!**

### 3. Laravel dijagnostika

```bash
php artisan eracun:test diagnostics
```

---

## 🔧 Troubleshooting

### Error: "unable to load provider legacy"

**Uzrok:** `OPENSSL_MODULES` nije postavljen ili putanja nije ispravna.

**Rješenje:**
```powershell
$env:OPENSSL_MODULES = "C:\Program Files\OpenSSL-Win64\bin"
Get-ChildItem $env:OPENSSL_MODULES -Filter "legacy.dll"
```

### Error: "could not load the shared library"

**Uzrok:** `legacy.dll` ne postoji - instalirana je Light verzija.

**Rješenje:** Reinstaliraj punu verziju OpenSSL-a.

### Certificate "Mac verify error: invalid password"

**Uzrok:** Password je neispravan (ovo NIJE legacy provider problem).

**Rješenje:** Provjeri password u `.env` fajlu:
```env
ERACUN_DEMO_CERT_PASSWORD="K2wbNnwGuFT4X9"
```

---

## 📚 Reference

- [OpenSSL 3.0 Migration Guide](https://www.openssl.org/docs/man3.0/man7/migration_guide.html)
- [OpenSSL Providers](https://www.openssl.org/docs/man3.0/man7/provider.html)
- [FINA PKI Demo CA](https://demo-pki.fina.hr/)
- [Win32 OpenSSL Downloads](https://slproweb.com/products/Win32OpenSSL.html)

---

## 🎯 Summary

| Aspekt | Detalj |
|--------|--------|
| **Problem** | FINA certifikati koriste RC2-40/3DES legacy enkripciju |
| **Greška** | `error:0308010C:digital envelope routines::unsupported` |
| **Rješenje** | Koristi OpenSSL CLI sa `-provider legacy -provider default` |
| **Windows Setup** | Postavi `OPENSSL_MODULES=C:\Program Files\OpenSSL-Win64\bin` |
| **PHP Integracija** | Koristi `exec()` za pozivanje OpenSSL CLI umjesto `openssl_pkcs12_read()` |
| **Validacija** | Provjeri MD5 modulus certifikata i key-a - moraju biti identični |
| **Scripts** | `extract_cert_to_pem.php` - ekstraktuje .p12 u .pem format |

---

**Zadnje ažuriranje:** 26. veljača 2026
