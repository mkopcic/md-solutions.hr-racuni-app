# NativePHP Mobile - Matrica Odluka

**Brzi pregled za donošenje odluka prije razvoja**

---

## 🎯 Ključne Odluke

### Odluka #1: Arhitektura Podataka

#### Opcija A: Standalone (Offline-Only)
```
Mobile App → SQLite → Lokalno samo
```

**Koristi ako:**
- Korisnici ne trebaju pristup s više uređaja
- Svaki uređaj je nezavisan
- Nemaš infrastrukturu za API

**Ne koristi ako:**
- Trebaju backup podaci
- Multi-device sync je potreban
- Želiš centraliziranu kontrolu

#### Opcija B: API-First (Hybrid) ⭐ PREPORUČENO
```
Mobile App ←REST API→ Server (MySQL)
     ↓
  SQLite Cache
```

**Koristi ako:**
- Trebaš backup-e (99% slučajeva)
- Multi-device podrška
- Centralizirana kontrola
- Cloud sync

**Dodatni development:**
- +2 tjedna za API development
- Laravel Sanctum auth setup
- Sync logika

---

### Odluka #2: Koje Podatke Držati na Mobilnom?

#### Scenario: Tvoja Račun Aplikacija

**Trenutne tablice:**
```
users
invoices
invoice_items
services
clients
activity_log
backups
sessions
...
```

**Mobilni pristup - Preporuka:**

| Tablica | Mobile SQLite | Razlog |
|---------|--------------|--------|
| `invoices` | ✅ Cache | Offline kreiranje + pregled |
| `invoice_items` | ✅ Cache | Vezano uz invoices |
| `services` | ✅ Full sync | Reference data, rijetko se mjenja |
| `clients` | ✅ Full sync | Potrebno za kreiranje računa |
| `users` | ❌ API Only | Auth preko API-ja |
| `activity_log` | 🟡 Partial | Samo zadnjih 30 dana |
| `backups` | ❌ Server Only | Preveliko za mobile |
| `sessions` | ❌ Server Only | Token-based auth |

**Sync strategija:**
```php
// Primer logike
class SyncService 
{
    public function syncDown() 
    {
        // Full sync (rijetko)
        $this->syncServices();     // Kompletna lista
        $this->syncClients();      // Kompletna lista
        
        // Partial sync (često)
        $this->syncRecentInvoices(days: 90);
        $this->syncActivityLog(days: 30);
    }
    
    public function syncUp()
    {
        // Šalji nove/izmjenjene račune na server
        $this->pushPendingInvoices();
    }
}
```

---

### Odluka #3: Feature Set za Mobile

#### Must-Have Features (v1.0)
- [ ] Pregled računa (readonly)
- [ ] Kreiranje novih računa
- [ ] PDF preview računa
- [ ] Offline rad
- [ ] Osnovna sinkronizacija

#### Nice-to-Have (v1.1+)
- [ ] Camera integration za slike dokumenata
- [ ] Push notifikacije za nove račune
- [ ] Biometric login
- [ ] Geolocation za klijente
- [ ] QR code scanning
- [ ] Share računa preko native share dialog

#### Kasnije ili Ne Sad
- [ ] Advanced reporting (ostavi za web)
- [ ] Full e-račun integration (kompleksno)
- [ ] Backup management (server only)
- [ ] Admin user management (web only)

---

## 📊 Usporedba: MySQL vs SQLite

### Što RADI na SQLite-u (bez promjena):

```php
// ✅ Standard Laravel Eloquent
Invoice::where('status', 'paid')->get();
Invoice::with('items')->latest()->paginate(20);

// ✅ Relations
$invoice->items;
$invoice->client;

// ✅ Transactions
DB::transaction(function() {
    // ...
});

// ✅ JSON columns (SQLite 3.38+)
$table->json('metadata');
```

### Što NE RADI ili Treba Prilagodbu:

```php
// ❌ MySQL specifične funkcije
DB::raw('YEAR(created_at)');           // Ne radi
DB::raw('strftime("%Y", created_at)'); // SQLite verzija

// ❌ ENUM tipovi
$table->enum('status', ['draft', 'paid']); 
// Koristi string + validation

// ⚠️ Foreign Keys - moraju biti eksplicitno omogućeni
Schema::enableForeignKeyConstraints();

// ⚠️ Altering tables - ograničeno u SQLite
// Ne možeš dropati column, mijenjati tip, itd.
// Moraš kreirati novu tablicu i kopirati podatke
```

---

## 🔄 Migration Strategy

### Pristup 1: Direktna SQLite Konverzija

```bash
# Eksportaj MySQL data
mysqldump obrt-racuni-laravel-app > backup.sql

# Konvertiraj u SQLite format (alat: mysql2sqlite)
mysql2sqlite backup.sql | sqlite3 database.sqlite

# Testiraj aplikaciju s SQLite bazom
DB_CONNECTION=sqlite php artisan serve
```

**Prednosti:**
- Brzo za proof-of-concept
- Vidi odmah što ne radi

**Nedostaci:**
- Možda trebaš refaktorirati migracije

### Pristup 2: Fresh Migrations (Preporučeno)

```bash
# Test s fresh SQLite bazom
DB_CONNECTION=sqlite php artisan migrate:fresh --seed

# Provjeri sve migracije
# Prilagodi one koje ne rade
```

**Prednosti:**
- Čisti setup
- Potvrđuje da migracije rade na SQLite

---

## 💾 Storage & Size Ograničenja

### SQLite Database Limits:
- **Max DB size:** 281 terabytes (praktično unlimited)
- **Max row size:** ~1GB
- **Max rows:** Praktično unlimited

### Mobile App Size:
- **Target veličina:** <50MB
- **iOS limit:** 4GB (praktično)
- **Android limit:** Varira po uređaju

### Preporuke za tvoju aplikaciju:

```php
// Ako računi imaju PDF-ove ili slike:
// NE spremi u SQLite kao BLOB!

// Umjesto:
$invoice->pdf_data = $pdfBlob; // ❌ Loše

// Koristi file system:
Storage::disk('local')->put("invoices/{$id}.pdf", $pdf); // ✅ Dobro

// Ili remote (ako koristiš API):
Storage::disk('s3')->put("invoices/{$id}.pdf", $pdf); // ✅ Još bolje
```

---

## 🛠️ Development Environment Setup

### Što ti Treba:

#### Za iOS Development:
- **macOS** (obavezno)
- **Xcode** (besplatno)
- **iOS Simulator** (dolazi s Xcode)
- **Apple Developer Account** ($99/god za distribution)
- **iOS uređaj** (za testiranje na pravom hardware-u)

#### Za Android Development:
- **Windows/Mac/Linux**
- **Android Studio** (besplatno)
- **Android Emulator** (dolazi s Android Studio)
- **Google Play Console** ($25 jednokratno)
- **Android uređaj** (za testiranje)

#### Za Development u Windows-u:
- ⚠️ **NE KORISTI WSL** - NativePHP ne radi u WSL-u
- Native Windows instalacija PHP/Laravel
- Windows Defender exclusions za `C:\temp` i project folder

---

## 📅 Realistični Timeline

### Faza 1: Proof of Concept (1 tjedan)
```
Dan 1-2: Setup i instalacija
Dan 3-4: Prebaci na SQLite, test migracije
Dan 5-7: Test basic app funkcionalnosti u emulatoru
```

### Faza 2: Core Development (3-4 tjedna)
```
Tjedan 1: Database sync logic (ako API-first)
Tjedan 2: UI/UX optimizacije za mobile
Tjedan 3: Native features integration
Tjedan 4: Offline functionality & caching
```

### Faza 3: Testing & Polish (2 tjedna)
```
Tjedan 1: Testing na različitim uređajima
Tjedan 2: Bug fixes i optimizacije
```

### Faza 4: Deployment (1 tjedan)
```
Dan 1-2: Production build
Dan 3-4: App Store submission prep
Dan 5-7: Wait for review + fixes
```

**UKUPNO: 7-8 tjedana za production-ready aplikaciju**

---

## 💡 Brza Provjera: Jesi li Spreman?

### Tehničke provjere:
- [ ] Razumiješ SQLite ograničenja?
- [ ] Imaš plan za backup podataka?
- [ ] Livewire komponente su mobile-friendly?
- [ ] Migracije rade na SQLite-u?
- [ ] Razumiješ razliku offline vs. API-first?

### Business provjere:
- [ ] Imaš budget za developer accounts?
- [ ] Korisnici žele mobile app?
- [ ] Možeš posvjetiti 2 mjeseca development-u?
- [ ] Imaš support plan za app updates?
- [ ] Privacy policy je spremna?

### Legal/Compliance:
- [ ] GDPR compliance za mobile?
- [ ] Terms of Service za app store?
- [ ] Privacy policy za app?
- [ ] Content policy compliance?

---

## 🎬 Akcijski Plan: Sljedeća 24h

### Ako želiš krenuti odmah:

1. **Napravi backup cijele aplikacije**
```bash
git branch feature/nativephp-mobile
git checkout feature/nativephp-mobile
```

2. **Test SQLite lokalno**
```bash
# U .env dodaj:
DB_CONNECTION=sqlite
DB_DATABASE=database/test.sqlite

# Kreiraj bazu
touch database/test.sqlite

# Pokreni migracije
php artisan migrate:fresh --seed
```

3. **Provjeri što ne radi**
```bash
# Pokreni app
php artisan serve

# Testiraj sve ključne funkcionalnosti
# - Kreiranje računa
# - Pregled računa
# - PDF generiranje
# itd.
```

4. **Odluči o arhitekturi**
- Standalone ili API-first?
- Napiši odluku u `docs/MOBILE_ARCHITECTURE_DECISION.md`

5. **Ako sve izgleda OK, instaliraj NativePHP**
```bash
composer require nativephp/mobile
php artisan native:install
```

---

## ❓ FAQ - Brze Odluke

**Q: Trebam li i web i mobile verziju?**  
A: Da! Web ostaje, mobile je dodatni kanal.

**Q: Mogu li koristiti istu Laravel app za oboje?**  
A: Da! Jedan kod, dva build outputa.

**Q: Što s postojećim korisnicima?**  
A: Web app i dalje radi, mobile je opcija.

**Q: Koliko košta per-user?**  
A: $0 - nema subscription-a (osim dev accounts)

**Q: Mogu li update-ati app bez store reviewa?**  
A: Ne za native kod, da za remote config/content.

**Q: Što ako korisnik nema internet?**  
A: App 100% radi offline (s SQLite cache-om).

**Q: Mogu li migrirati postojeće MySQL podatke?**  
A: Može, ali komplicirano. API-first je lakši.

**Q: Trebam li učiti Swift/Kotlin?**  
A: Ne! Samo PHP.

---

**ODLUKA POTREBNA:**  
☐ Standalone (offline-only)  
☐ API-First (hybrid, sa syncom)

**SLJEDEĆI KORAK:**  
Testiraj SQLite migraciju → Procijeni effort → Odluči

---

*Dokumentacija kreirana: 26/02/2026*  
*Status: Spremno za odluku i implementaciju*
