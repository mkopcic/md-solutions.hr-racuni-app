# NativePHP Mobile - Plan i Dokumentacija

**Verzija dokumentacije:** 1.0  
**Datum:** 26. veljače 2026  
**Status:** PLANIRANJE - NIŠTA NIJE INSTALIRANO

---

## 📱 Što je NativePHP Mobile?

NativePHP Mobile je revolucionarni paket koji omogućava pisanje **potpuno nativnih mobilnih aplikacija** koristeći PHP i Laravel. 

### Ključne karakteristike:

- ✅ **Embedded PHP Runtime** - PHP runtime se pakira zajedno s aplikacijom
- ✅ **Bez Web Servera** - Aplikacija radi potpuno offline na uređaju
- ✅ **Swift/Kotlin Shell** - Native wrapper za iOS i Android
- ✅ **<50MB veličina** - Male i brze aplikacije
- ✅ **Livewire/Volt podrška** - Možete koristiti postojeći Livewire kod!
- ✅ **Native API pristup** - Kamera, biometrija, push notifikacije, GPS, itd.
- ✅ **Cross-platform** - iOS i Android iz iste code base

### Kako radi?

```
┌─────────────────────────────────────────┐
│     Swift (iOS) / Kotlin (Android)      │
│              Shell App                   │
├─────────────────────────────────────────┤
│       Pre-compiled PHP Runtime          │
│         (PHP 8.3+)                      │
├─────────────────────────────────────────┤
│      Custom PHP Extension               │
│    (Native Function Bridge)             │
├─────────────────────────────────────────┤
│      Laravel Aplikacija                 │
│   (Livewire, Blade, Tailwind)          │
├─────────────────────────────────────────┤
│      SQLite Database                    │
│     (On-device storage)                 │
└─────────────────────────────────────────┘
```

---

## 💾 Podrška za Bazu Podataka

### ⚠️ VAŽNO: Samo SQLite!

NativePHP Mobile podržava **ISKLJUČIVO SQLite** bazu podataka. MySQL i PostgreSQL **NISU** podržani.

#### Zašto samo SQLite?

**Sigurnosni razlozi:**
- Mobile aplikacije se mogu reverse-engineerati
- Database credentials bi bili izloženi u app binary-ju
- Direktne konekcije zaobilaze sigurnosne slojeve (rate limiting, access control)
- Mrežna konekcija je nepouzdana na mobilnim uređajima

#### Kako NativePHP Upravlja Bazom?

**Automatska konfiguracija:**
```php
// NativePHP AUTOMATSKI:
// 1. Prebacuje na SQLite pri build-u
// 2. Kreira bazu u app container-u
// 3. Pokreće migrate pri svakom startu (ako je potrebno)
```

**Nema potrebe za posebnom konfiguracijom!**

### Migracije

```bash
# Normalno kreiraj migracije kao i uvijek
php artisan make:migration create_invoices_table

# NativePHP će ih automatski pokrenuti pri startu aplikacije
```

**⚠️ Važno za migracije:**
- SQLite ima različita ograničenja od MySQL-a
- Foreign key constraints moraju biti eksplicitno omogućeni (prije Laravel 11)
- Testiraj migracije na **production build** prije release-a
- Migracije rade i za nove instalacije i za update-e

### Lokacija Baze

```php
// iOS lokacija:
/var/mobile/Containers/Data/Application/{UUID}/Documents/database.sqlite

// Android lokacija:
/data/data/{package.name}/databases/database.sqlite
```

**Napomena:**
- Nemaš remote pristup bazi (nije server!)
- Ako korisnik obriše app, briše se i baza
- Sve je lokalno na uređaju

---

## 🔄 Tvoja Trenutna Aplikacija vs. Mobile

### Trenutno stanje:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=obrt-racuni-laravel-app
DB_USERNAME=root
DB_PASSWORD=
```

### U NativePHP Mobile:

```env
# Ovo će NativePHP AUTOMATSKI prebaciti na:
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/app/container/database.sqlite
# MySQL credentials se ignoriraju u mobile build-u
```

---

## 🏗️ Arhitektura: Dva Pristupa

### Pristup 1: Standalone Mobile App (Potpuno Offline)

```
┌──────────────────────┐
│   Mobile Device      │
│                      │
│  ┌────────────────┐  │
│  │  Laravel App   │  │
│  │  (PHP Runtime) │  │
│  └────────┬───────┘  │
│           │          │
│  ┌────────▼───────┐  │
│  │ SQLite Database│  │
│  └────────────────┘  │
└──────────────────────┘

✅ Sve lokalno
✅ Potpuno offline
❌ Nema sync između uređaja
❌ Backup je problem
```

**Idealno za:**
- Invoice kreiranje offline
- Standalone kalkulatori
- Lokalni izvještaji

### Pristup 2: API-First (Hybrid) ⭐ **PREPORUČENO**

```
┌──────────────────────┐         ┌────────────────────┐
│   Mobile Device      │         │   API Server       │
│                      │         │   (Laravel)        │
│  ┌────────────────┐  │         │                    │
│  │  Laravel App   │  │◄────────┤  REST API          │
│  │  (PHP Runtime) │  │  HTTPS  │  (Sanctum Auth)    │
│  └────────┬───────┘  │         │                    │
│           │          │         │  ┌──────────────┐  │
│  ┌────────▼───────┐  │         │  │ MySQL DB     │  │
│  │ SQLite Cache   │  │         │  │ (Production) │  │
│  │ (Offline data) │  │         │  └──────────────┘  │
│  └────────────────┘  │         └────────────────────┘
└──────────────────────┘
     Sync when online

✅ Radi offline (cache)
✅ Sync s cloud-om
✅ Backup automatski
✅ Multi-device podrška
```

**Idealno za:**
- Tvoju aplikaciju s računima
- Multi-user scenariji
- Centralizirana kontrola

---

## 🎯 Plan Implementacije

### Faza 1: Istraživanje i Priprema (1-2 dana)

**Zadaci:**
- [ ] Instalirati NativePHP mobile (`composer require nativephp/mobile`)
- [ ] Pokrenuti `php artisan native:install`
- [ ] Postaviti environment varijable u `.env`
- [ ] Pregledati strukturu `nativephp/` direktorija
- [ ] Testirati build u emulatoru

**Konfiguracijske varijable:**
```env
# Dodaj u .env
NATIVEPHP_APP_ID=hr.mellon.obrtracuni
NATIVEPHP_APP_VERSION="1.0.0"
NATIVEPHP_APP_VERSION_CODE="1"
NATIVEPHP_DEVELOPMENT_TEAM={AppleDevTeamID}  # Za iOS
```

### Faza 2: Analiza Database Strategije (2-3 dana)

**Odluke koje trebaš donijeti:**

#### Opcija A: Potpuno Offline (Jednostavno)
```php
// Sve ostaje isto, samo prebaciti na SQLite
// Mobile koristi lokalni SQLite
// Nema synca s serverom
```

**Prednosti:**
- Brza implementacija
- Bez dodatne infrastrukture

**Nedostaci:**
- Nema backup-a
- Podaci samo na jednom uređaju
- Nema cloud synca

#### Opcija B: API-First Pristup (Profesionalno) ⭐

**Backend API (Laravel na serveru):**
```php
// routes/api.php
use Laravel\Sanctum\Sanctum;

Route::middleware('auth:sanctum')->group(function () {
    // Invoices API
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    
    // Sync API
    Route::post('/sync/pull', [SyncController::class, 'pull']);
    Route::post('/sync/push', [SyncController::class, 'push']);
});
```

**Mobile App (NativePHP):**
```php
// app/Services/SyncService.php
class SyncService
{
    public function syncInvoices()
    {
        if (! $this->hasConnection()) {
            return $this->useCachedData();
        }
        
        $token = SecureStorage::get('api_token');
        
        // Pull latest from API
        $response = Http::withToken($token)
            ->get(config('app.api_url') . '/api/invoices');
        
        // Update local SQLite cache
        $this->updateLocalCache($response->json());
    }
}
```

**Prednosti:**
- Podaci sigurni u cloud-u
- Multi-device sync
- Offline functionality kroz cache
- Centralizirana kontrola

**Nedostaci:**
- Zahtijeva API development
- Kompleksnije

### Faza 3: SQLite Migracija (3-5 dana)

**Testiranje migracijskih skripti:**

```bash
# 1. Testiraj sve postojeće migracije na SQLite
DB_CONNECTION=sqlite php artisan migrate:fresh --seed

# 2. Provjeri kompatibilnost
# MySQL -> SQLite razlike:
# - TINYINT -> INTEGER
# - ENUM -> TEXT with CHECK constraint
# - JSON -> TEXT (SQLite 3.38+ ima native JSON)
# - Foreign keys -> moraju biti eksplicitno omogućeni
```

**Prilagodbe za SQLite:**
```php
// database/migrations/xxxx_create_invoices_table.php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_number')->unique();
    $table->decimal('total', 10, 2);
    
    // Umjesto ENUM-a
    $table->string('status')->default('draft'); 
    // Dodaj check constraint ako je potrebno
    
    $table->foreignId('user_id')
        ->constrained()
        ->onDelete('cascade'); // Check SQLite FK support
    
    $table->timestamps();
});
```

**Test checklist:**
- [ ] Sve migracije rade na SQLite-u
- [ ] Foreign keys rade ispravno
- [ ] Indexi su kreirani
- [ ] Seed data se upisuje
- [ ] Queries rade (neki MySQL sintaks ne radi u SQLite)

### Faza 4: UI/UX Prilagodbe (5-7 dana)

**Livewire/Volt komponente moraju biti mobile-friendly:**

```php
// resources/views/livewire/invoices/create.blade.php
<div class="mobile-optimized">
    {{-- Već imaš mobile CSS! --}}
    <div class="md:hidden"> {{-- Mobile view --}}
        {{-- Tvoje postojeće mobile karte --}}
    </div>
    
    <div class="hidden md:block"> {{-- Desktop view --}}
        {{-- Tvoje desktop tablice --}}
    </div>
</div>
```

**Što provjeriti:**
- [ ] Responsive dizajn (već imaš!)
- [ ] Touch-friendly buttons (minimum 44x44px)
- [ ] Native input fields (number, date, etc.)
- [ ] Loading states
- [ ] Offline indicators
- [ ] Error handling

### Faza 5: Native Features Integration (3-5 dana)

**Dodaj native funkcionalnosti:**

```php
use Native\Laravel\Facades\Camera;
use Native\Laravel\Facades\SecureStorage;
use Native\Laravel\Facades\Notification;

// 1. Slikanje računa/dokumenata
$photo = Camera::takePicture();

// 2. Sigurno spremanje tokena
SecureStorage::put('api_token', $token);

// 3. Push notifikacije
Notification::send([
    'title' => 'Račun kreiran',
    'body' => "Račun #{$invoice->number} je spremljen"
]);
```

**Native API-ji dostupni:**
- Camera & Gallery
- Biometric Auth (Face ID, Touch ID, Fingerprint)
- Push Notifications
- GPS / Location
- Secure Storage
- Haptic Feedback
- Deep Links
- Flashlight
- Share Dialog
- Native File Picker

### Faza 6: Build & Test (5-7 dana)

```bash
# 1. Build za development
php artisan native:run
# Odaberi platform (iOS Simulator / Android Emulator)

# 2. Test na stvarnim uređajima
# iOS: Potreban Developer Mode uključen
# Android: USB debugging uključen

# 3. Production build
php artisan native:build --production
```

**Test scenariji:**
- [ ] Nova instalacija (fresh install)
- [ ] Update postojeće aplikacije
- [ ] Offline functionality
- [ ] Background sync (ako koristiš API)
- [ ] Migracije rade ispravno
- [ ] Performance na starijim uređajima
- [ ] Battery usage

### Faza 7: App Store Deployment (2-3 dana)

**iOS (App Store):**
- Apple Developer Account ($99/godišnje)
- App Store Connect setup
- Privacy policy
- Screenshots & metadata
- Review process (1-3 dana)

**Android (Play Store):**
- Google Play Console ($25 jednokratno)
- App signing
- Privacy policy
- Screenshots & metadata
- Review process (nekoliko sati)

---

## 📊 Usporedba: Web vs. Mobile

| Feature | Web App (Trenutno) | Mobile App (NativePHP) |
|---------|-------------------|------------------------|
| **Platform** | Browser | iOS & Android Native |
| **Database** | MySQL (remote) | SQLite (on-device) |
| **Offline** | ❌ Ne | ✅ Da |
| **Install** | N/A | App Store / Play Store |
| **Push Notify** | Limited (PWA) | ✅ Full native |
| **Camera** | HTML5 API | ✅ Native API |
| **Performance** | Ovisi o vezi | ⚡ Native speed |
| **Update** | Instant | App Store review |
| **Distribution** | URL | Store approval needed |

---

## 💰 Troškovi

### Developer Accounts:
- **Apple Developer:** $99/godišnje (iOS)
- **Google Play Console:** $25 jednokratno (Android)

### Hosting (ako koristiš API pristup):
- **API Server:** Postojeći hosting
- **Database:** Trenutni MySQL

### Development Time:
- **Priprema:** 1-2 tjedna
- **Development:** 3-4 tjedna
- **Testing:** 1-2 tjedna
- **Deployment:** 3-5 dana

**Ukupno:** ~2 mjeseca za full production-ready app

---

## 🚀 Quick Start Naredbe (Kada budeš spreman)

```bash
# ⚠️ NE POKREĆI OVO SADA - SAMO ZA REFERENCU!

# 1. Instalacija paketa
composer require nativephp/mobile

# 2. Setup environment
# Dodaj u .env:
# NATIVEPHP_APP_ID=hr.mellon.obrtracuni
# NATIVEPHP_APP_VERSION="1.0.0"
# NATIVEPHP_APP_VERSION_CODE="1"

# 3. Install NativePHP
php artisan native:install

# 4. Run u development mode
php artisan native:run

# 5. Build za production
php artisan native:build --production
```

---

## 📚 Resursi

- **Dokumentacija:** https://nativephp.com/docs/mobile/3/getting-started/introduction
- **Database Guide:** https://nativephp.com/docs/mobile/3/concepts/databases
- **GitHub:** https://github.com/NativePHP/mobile-air
- **Discord:** https://discord.gg/nativephp
- **Showcase:** https://nativephp.com/showcase/mobile

---

## ✅ Sljedeći Koraci - Prioriteti

1. **Odluči o strategiji baze podataka**
   - Standalone (offline only) vs. API-first?
   
2. **Testiraj SQLite kompatibilnost**
   - Pokreni migracije na SQLite lokalno
   
3. **Napravi proof-of-concept**
   - Install NativePHP
   - Build jednostavnu verziju app-a
   - Test na emulatoru
   
4. **Planiraj API (ako odabereš API-first)**
   - Laravel Sanctum setup
   - Sync logic
   - Offline caching strategy

---

## ⚠️ Važne Napomene

- **Nema MySQL-a u mobile app-u** - Samo SQLite!
- **Filament zahtijeva `intl` extension** - Instaliraj ICU PHP binaries ako koristiš Filament
- **App Store proces traje** - Računaj na 1-3 dana review za iOS
- **Testiraj thoroughly** - Migracije su kritične!
- **Backup strategija** - Cloud sync ili export funkcionalnost je MUST

---

**Status:** Dokumentacija spremna za implementaciju  
**Autor:** GitHub Copilot (AI Assistant)  
**Review:** Potreban pregled i odluka o strategiji prije implementacije
