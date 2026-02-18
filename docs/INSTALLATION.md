# Instalacija Projekta

## Detalji Projekta

**Ime:** Računi Obrt  
**Repozitorij:** https://github.com/mkopcic/racuni-obrt  
**Lokalni URL:** https://obrt-racuni-laravel-app.test/  
**Baza podataka:** obrt-racuni-laravel-app (MySQL, root bez passworda)

---

## Okruženje

- **OS:** Windows (Laragon)
- **PHP:** ^8.5
- **Laravel:** ^12.0
- **Node.js:** npm dependencies
- **Web Server:** Laragon
- **Mail:** Mailpit (SMTP port 1025, Web interface: http://localhost:8025)

---

## Instalirani Paketi

### PHP Dependencies (Composer)

#### Production
- `laravel/framework` ^12.0 - Laravel framework
- `laravel/tinker` ^2.10.1 - REPL za Laravel
- `laravel/ai` * - Laravel AI SDK
- `livewire/flux` ^2.1.1 - Flux UI komponente (FREE verzija)
- `livewire/volt` ^1.7.0 - Livewire Volt
- `barryvdh/laravel-dompdf` ^3.1 - PDF generiranje
- `simplesoftwareio/simple-qrcode` ^4.2.0 - QR kod generiranje (HUB3 plaćanje)
- `robrichards/xmlseclibs` ^3.1 - XML potpis za e-Račun integraciju
- `opcodesio/log-viewer` ^3.17 - Web viewer za logove
- `spatie/laravel-activitylog` ^4.10 - Activity logging

#### Development
- `laravel/boost` ^1.1 - Laravel Boost MCP server
- `laravel/pint` ^1.18 - Code style formatter (PSR-12)
- `laravel/sail` ^1.41 - Docker development environment
- `laravel/pail` ^1.2.2 - Log viewer CLI
- `barryvdh/laravel-debugbar` ^3.15 - Debug bar
- `pestphp/pest` ^3.8 - Testing framework
- `pestphp/pest-plugin-laravel` ^3.2 - Laravel integration za Pest
- `laravel-lang/common` ^6.7 - Jezične lokalizacije
- `laravel-lang/publisher` ^16.6 - Publisher za lokalizacije
- `fakerphp/faker` ^1.23 - Fake data generator
- `mockery/mockery` ^1.6 - Mocking library
- `nunomaduro/collision` ^8.6 - Error handling

### Frontend Dependencies (npm)

- `vite` ^6.0 - Build tool
- `laravel-vite-plugin` ^1.0 - Vite integration za Laravel
- `tailwindcss` ^4.0.7 - CSS framework
- `@tailwindcss/vite` ^4.0.7 - Tailwind Vite plugin
- `autoprefixer` ^10.4.20 - CSS vendor prefixes
- `axios` ^1.7.4 - HTTP client
- `concurrently` ^9.0.1 - Run multiple commands

---

## Struktura Baze Podataka

### Migracije

1. **Laravel Core Tabele:**
   - `users` - Korisnici sistema
   - `cache` - Cache storage
   - `jobs` - Queue jobs

2. **Business Logic:**
   - `businesses` - Podaci o obrtu (includes logo_path, in_vat_system, business_space_label, cash_register_label)
   - `customers` - Kupci
   - `invoices` - Računi (includes invoice_number, invoice_year, invoice_type, payment_method, subtotal, tax_total)
   - `invoice_items` - Stavke računa (includes unit, tax_rate, tax_amount)
   - `incoming_invoices` - Dolazni računi (e-Račun)
   - `incoming_invoice_items` - Stavke dolaznih računa
   - `eracun_logs` - e-Račun komunikacija logovi
   - `kpr_entries` - KPR unosi
   - `tax_brackets` - Porezne stope
   - `services` - Usluge
   - `activity_log` - Activity log (Spatie)

---

## Konfiguracija

### .env Postavke

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=https://obrt-racuni-laravel-app.test

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=obrt-racuni-laravel-app
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_ENCRYPTION=null

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Koraci Instalacije

1. **Kloniranje repozitorija:**
   ```bash
   git clone https://github.com/mkopcic/racuni-obrt.git .
   ```

2. **Instalacija Composer dependencies:**
   ```bash
   composer install
   ```

3. **Kreiranje .env fajla:**
   ```bash
   copy .env.example .env
   ```

4. **Generiranje application key:**
   ```bash
   php artisan key:generate
   ```

5. **Pokretanje migracija:**
   ```bash
   php artisan migrate
   ```

6. **Instalacija npm packages:**
   ```bash
   npm install
   ```

7. **Build frontend assets:**
   ```bash
   npm run build
   ```

---

## Pokretanje Development Servera

### Opcija 1: Pojedinačno
```bash
php artisan serve
npm run dev
php artisan queue:listen
```

### Opcija 2: Sve odjednom (preporučeno)
```bash
composer run dev
```
Ova komanda pokreće:
- Laravel development server (http://127.0.0.1:8000)
- Queue listener
- Vite dev server (HMR)

---

## Laravel Boost

Laravel Boost MCP server je instaliran i konfigurisan sa sljedećim guidelines:

- `boost` - Core Boost functionality
- `foundation` - Foundation rules
- `php` - PHP best practices
- `laravel/core` - Laravel core conventions
- `laravel/v12` - Laravel 12 specific features
- `fluxui-free/core` - Flux UI free edition
- `livewire/core` - Livewire core
- `livewire/v3` - Livewire v3 specifics
- `volt/core` - Livewire Volt
- `tailwindcss/core` - Tailwind CSS
- `tailwindcss/v4` - Tailwind v4 specifics
- `pest/core` - Pest testing
- `pint/core` - Laravel Pint formatter
- `tests` - Testing enforcement

### Boost Alati
Boost pruža:
- `search-docs` - Pretraga dokumentacije specifične za instalirane verzije
- `tinker` - PHP REPL
- `database-query` - Direktan pristup bazi
- `browser-logs` - Čitanje browser logova
- `list-artisan-commands` - Lista Artisan komandi
- `get-absolute-url` - Generiranje URL-ova

---

## Testiranje

```bash
# Svi testovi
php artisan test

# Specifičan test fajl
php artisan test tests/Feature/ExampleTest.php

# Filter po imenu
php artisan test --filter=testName
```

---

## Code Style

```bash
# Formatiranje koda (Laravel Pint)
vendor/bin/pint

# Samo dirty files
vendor/bin/pint --dirty
```

---

## Dokumentacija

### Changelog i Izmjene

- **[PDF Redesign Changelog](CHANGELOG_PDF_REDESIGN.md)** - Detaljne izmjene PDF sustava, novih polja, QR kodova i brojanja računa (16.02.2026)

### Tehnička Dokumentacija

- **Laravel AI SDK** - [LARAVEL_AI_SDK.md](LARAVEL_AI_SDK.md)
- **Installation Guide** - Ovaj dokument
- **e-Račun Integracija** - [FINA_E_RACUN_INTEGRACIJA.md](FINA_E_RACUN_INTEGRACIJA.md)
- **e-Račun Setup** - [e-racun/ERACUN_SETUP.md](e-racun/ERACUN_SETUP.md)
- **e-Račun Arhitektura** - [e-racun/DATABASE_ARCHITECTURE.md](e-racun/DATABASE_ARCHITECTURE.md)
- **Fiskalizacija** - [fiskalizacija/fiskalizacija.md](fiskalizacija/fiskalizacija.md)

---

## Korisni Linkovi

- **Aplikacija:** https://obrt-racuni-laravel-app.test/
- **Mailpit:** http://localhost:8025
- **Log Viewer:** https://obrt-racuni-laravel-app.test/log-viewer
- **Debug Bar:** Vidljiv u development modu

---

## Stack Overview

- **Backend:** Laravel 12 + Livewire 3 + Volt
- **Frontend:** Tailwind CSS 4 + Flux UI (Free) + Vite 6
- **Database:** MySQL
- **Testing:** Pest 3
- **Mail:** Mailpit (local)
- **Queue:** Database driver
- **Cache:** Database driver
- **Session:** Database driver
