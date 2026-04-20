# 📊 Računi Obrt - Aplikacija za upravljanje računima

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind%20CSS-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Flux UI](https://img.shields.io/badge/Flux%20UI-2-FB70A9?style=for-the-badge)](https://fluxui.dev)

Aplikacija za izradu i upravljanje računima za obrtnike bazirana na Laravel 12, Livewire 4 i Flux UI frameworku. Radi na produkcijskom serveru (Hetzner VPS, domena `md-solutions.hr`).

## 🌐 Produkcija

- **Server:** Hetzner VPS, Linux
- **Domena:** md-solutions.hr
- **PHP:** 8.4.20 / Laravel 12 / MySQL

## 📋 Funkcionalnosti

### 👥 Kupci (Customers)

- Pregled, dodavanje, uređivanje i brisanje kupaca
- Pretraživanje po nazivu ili OIB-u
- Statistike (ukupno kupaca, aktivni)
- Export u Excel/CSV

### 💼 Računi (Invoices)

- Pregled s filtriranjem po statusu, vremenskom razdoblju i kupcu
- Tipovi računa s neovisnim sekvencama brojanja (format `broj/mjesec/1`)
- Načini plaćanja: Virman, Gotovina, Kartica
- PDV kalkulacija po stavkama (25%, 13%, 5%, 0%)
- Brz odabir usluga iz kataloga pri kreiranju
- **Profesionalni PDF računi** — plava tema, HUB3 QR kod, detaljna PDV razrada
- Evidencija plaćanja, download PDF-a
- Export u Excel/CSV
- **e-Račun slanje** — integracija s FINA B2B sustavom (vidi dolje)

### 📝 Ponude (Quotes)

- Kompletno upravljanje ponudama (nacrt, poslano, prihvaćeno, odbijeno, isteklo)
- Konverzija ponude u račun jednim klikom
- PDF generiranje i slanje emailom
- Vlastita sekvenca brojanja, `valid_until` datum
- Export u Excel/CSV

### 🛍️ Usluge (Services)

- Katalog predložaka usluga za brže kreiranje računa
- Dodavanje, uređivanje, brisanje usluga
- Statistike korištenja po usluzi

### 📚 Knjiga prometa (KPR)

- Automatsko generiranje KPR zapisa iz računa
- Pregled po mjesecima i godinama, ukupni promet

### 💸 Porezni razredi (Tax Brackets)

- Paušalni porezni razredi: raspon prihoda, osnova, iznos poreza

### 🏢 Podaci o obrtu (Business Settings)

- Naziv, OIB, adresa, kontakt, bankovni račun (IBAN)
- Upload loga
- U PDV sustavu (checkbox)
- Oznaka poslovnog prostora i blagajne (za fiskalizaciju/e-Račun)

### 📧 e-Račun B2B (FINA integracija)

Kompletna implementacija e-Račun B2B standarda. Čeka se aktivacija OIB-a u FINA demo sustavu — sav kod je spreman.

- **UBL 2.1 XML generator** prema CIUS-HR-2025 specifikaciji
- **XMLDSig potpis** UBL dokumenta (RSA-SHA256, Exclusive C14N)
- **WS-Security potpis** SOAP headera (BinarySecurityToken, X509v3)
- **HTTPS client certifikat autentifikacija** (Guzzle, TLS handshake potvrđen)
- **Demo certifikat** od FINA-e: Fina Demo CA 2020, validan do 31.07.2030.
- **Izlazni e-Računi** — slanje na FINA, praćenje statusa, retry
- **Ulazni e-Računi** — pregled primljenih računa
- **EracunLog** — puni audit trail svakog API poziva
- **e-Račun Postavke** — Livewire stranica za upravljanje konfiguracijom i certifikatom
- **9 Pest feature testova** (UBL, XMLDSig, WS-Security, FINA mock)

**Konfig ključevi u `.env`:**
```
ERACUN_ENVIRONMENT=demo
ERACUN_DEMO_URL=          # Čeka se od FINA-e nakon aktivacije
ERACUN_SUPPLIER_OIB=86058362621
ERACUN_SUPPLIER_NAME="MD SOLUTIONS VL. MARINA KOPČIĆ"
ERACUN_SUPPLIER_ADDRESS="KARDINALA FRANJE ŠEFERA 29"
ERACUN_SUPPLIER_CITY=ČEPIN
ERACUN_SUPPLIER_POSTAL_CODE=31431
ERACUN_SUPPLIER_IBAN=HR9023400091160578001
```

**Dokumentacija:**
- `docs/ERACUN_SAZETEK.md` — sažetak implementacije
- `docs/eracun-ws-security-implementacija.md` — WS-Security detalji
- `docs/fina-kako-implementirati.md` — implementacijski vodič
- `docs/fina-mail/` — pripremljeni dokumenti za slanje FINI (UBL primjer, SOAP primjer, tech doc)

### 📥 CSV Import

- `php artisan invoices:import-csv {file}` — import računa iz CSV-a
- Isti format kao Excel BAZA sheet, automatski skip duplikata s logiranjem

### 📋 Pregled logova & Activity Logs

- Log Viewer (`/logs`) — filtriranje po razini, pretraga, paginacija
- Activity Logs — automatsko bilježenje svih CRUD akcija, filtriranje, "Obriši sve logove"

### ✨ Ostalo

- Dashboard sa statistikama i grafikonima
- Scheduler za automatske taskove (email izvještaji, status sinkronizacija)
- Dark mode podrška kroz cijelu aplikaciju
- 🇭🇷 Prilagođeno hrvatskom jeziku i valuti (€)

## 🛠️ Tehnologije

| | |
|---|---|
| **Backend** | Laravel 12, PHP 8.4, MySQL |
| **Frontend** | Livewire 4, Volt, Flux UI 2, Tailwind CSS 4, Alpine.js |
| **PDF** | DomPDF, HUB3 QR kod (Simple QR Code) |
| **e-Račun** | robrichards/xmlseclibs (XMLDSig, RSA-SHA256), Guzzle (SOAP/HTTPS) |
| **Export** | Maatwebsite/Excel (Excel + CSV) |
| **Auth** | Laravel Fortify |
| **Tooling** | Laravel Boost (MCP), Laravel Pint, Pest 3 |

## 🚀 Instalacija

```bash
git clone https://github.com/mkopcic/md-solutions.hr-racuni-app.git
cd md-solutions.hr-racuni-app

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Objavi Log Viewer assete
php artisan vendor:publish --tag=log-viewer-assets --force

php artisan serve
```

## 📊 Struktura baze podataka

| Model | Opis |
|---|---|
| `businesses` | Podaci o obrtu (naziv, OIB, IBAN, logo, e-Račun/fiskalizacija polja) |
| `customers` | Kupci (naziv, OIB, adresa, kontakt) |
| `invoices` | Računi (broj, datum, kupac, status, PDV, način plaćanja) |
| `invoice_items` | Stavke računa (naziv, količina, cijena, PDV stopa, j.mj.) |
| `quotes` | Ponude (broj, valid_until, status, veza na račun ako konvertirana) |
| `quote_items` | Stavke ponude |
| `services` | Katalog usluga |
| `kpr_entries` | Knjiga prometa (mjesec, godina, iznos) |
| `tax_brackets` | Paušalni porezni razredi |
| `incoming_invoices` | Primljeni e-Računi od dobavljača (FINA) |
| `incoming_invoice_items` | Stavke ulaznih e-Računa |
| `eracun_logs` | Audit trail svih FINA API poziva (status, XML, greška) |

## 🤖 Laravel AI SDK

Aplikacija koristi **Laravel AI SDK** za AI mogućnosti:

- Unified API za rad s AI providerima (OpenAI, Anthropic, Gemini, xAI, Mistral, Ollama...)
- AI Agenti s conversation memory i custom tools
- Image generation, text-to-speech, transcription, embeddings

📚 Detalji: [docs/LARAVEL_AI_SDK.md](docs/LARAVEL_AI_SDK.md)

## 📚 Dokumentacija

| Datoteka | Sadržaj |
|---|---|
| [docs/INSTALLATION.md](docs/INSTALLATION.md) | Setup, konfiguracija, struktura projekta |
| [docs/USER_GUIDE.md](docs/USER_GUIDE.md) | Upute za korištenje aplikacije |
| [docs/QUICK_REFERENCE.md](docs/QUICK_REFERENCE.md) | Brzi pregled funkcionalnosti i izmjena |
| [docs/LARAVEL_AI_SDK.md](docs/LARAVEL_AI_SDK.md) | AI integracije |
| [docs/ERACUN_SAZETEK.md](docs/ERACUN_SAZETEK.md) | e-Račun implementacija — sažetak |
| [docs/eracun-ws-security-implementacija.md](docs/eracun-ws-security-implementacija.md) | WS-Security potpis SOAP headera |
| [docs/fina-kako-implementirati.md](docs/fina-kako-implementirati.md) | Vodič za FINA e-Račun implementaciju |
| [docs/FINA_E_RACUN_INTEGRACIJA.md](docs/FINA_E_RACUN_INTEGRACIJA.md) | Detaljna FINA integracija |
| [docs/fina-mail/](docs/fina-mail/) | Dokumenti za slanje FINI (UBL primjer, SOAP primjer, tech doc, email pristupnica) |
| [docs/CHANGELOG_PDF_REDESIGN.md](docs/CHANGELOG_PDF_REDESIGN.md) | PDF sustav izmjene |

## 🚀 Laravel Boost

Aplikacija koristi **Laravel Boost** — MCP server koji AI agentima daje pristup aplikaciji:

- `application-info`, `list-routes`, `list-artisan-commands`
- `database-query`, `database-schema`
- `tinker` — izvršavanje PHP koda za debugging
- `read-log-entries`, `browser-logs`
- `get-config`, `get-absolute-url`
- `search-docs` — semantička pretraga 17,000+ dokumenata Laravel ekosustava
