# E-Račun Arhitektura Baze Podataka

Ova dokumentacija opisuje strukturu baze podataka za FINA e-Račun B2B integraciju.

## Pregled

Sustav koristi **3 glavne tablice**:
1. **`eracun_logs`** - Centralni log za sve transakcije (izlazni i ulazni računi)
2. **`incoming_invoices`** - Primljeni računi od dobavljača
3. **`incoming_invoice_items`** - Stavke primljenih računa

## Tablice

### 1. eracun_logs

Centralna tablica koja prati sve e-Račun transakcije u oba smjera.

```sql
CREATE TABLE eracun_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NULL,              -- FK za izlazne račune (invoices)
    incoming_invoice_id BIGINT UNSIGNED NULL,     -- FK za ulazne račune
    direction ENUM('outgoing', 'incoming'),       -- Smjer transakcije
    message_id VARCHAR(255) NOT NULL,             -- Jedinstveni ID poruke
    fina_invoice_id VARCHAR(255) NULL,            -- FINA ID računa
    ubl_xml LONGTEXT NULL,                        -- UBL 2.1 XML računa
    request_xml LONGTEXT NULL,                    -- SOAP request
    response_xml LONGTEXT NULL,                   -- SOAP response
    status ENUM(...) DEFAULT 'pending',           -- Lokalni status
    fina_status ENUM(...) NULL,                   -- FINA status (polling)
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    sent_at TIMESTAMP NULL,
    retried_at TIMESTAMP NULL,
    status_checked_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE(message_id),
    INDEX idx_direction (direction),
    INDEX idx_status (status),
    INDEX idx_fina_status (fina_status),
    INDEX idx_invoice_direction (invoice_id, direction),
    INDEX idx_incoming_direction (incoming_invoice_id, direction),
    
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (incoming_invoice_id) REFERENCES incoming_invoices(id) ON DELETE CASCADE
);
```

**Ključna polja:**
- `direction`: Разликuje izlazne ('outgoing') i ulazne ('incoming') račune
- `status`: Lokalni status slanja/primanja (pending, sending, sent, accepted, rejected, failed)
- `fina_status`: Status na FINA sustavu (RECEIVED, RECEIVING_CONFIRMED, APPROVED, REJECTED, PAYMENT_RECEIVED)
- `retry_count`: Broj pokušaja ponovnog slanja (max 3)

### 2. incoming_invoices

Tablica za primljene račune od dobavljača.

```sql
CREATE TABLE incoming_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Podaci o dobavljaču
    supplier_oib VARCHAR(11) NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_address VARCHAR(255) NULL,
    supplier_city VARCHAR(100) NULL,
    supplier_postal_code VARCHAR(10) NULL,
    supplier_iban VARCHAR(34) NULL,
    
    -- Podaci o računu
    invoice_number VARCHAR(50) NOT NULL,
    fina_invoice_id VARCHAR(255) UNIQUE NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    payment_method ENUM('TRANSFER', 'CASH', 'CARD') NOT NULL,
    
    -- Iznosi (u EUR)
    subtotal DECIMAL(10,2) NOT NULL,
    tax_total DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    
    -- XML i dodatne informacije
    ubl_xml LONGTEXT NULL,
    notes TEXT NULL,
    rejection_reason TEXT NULL,
    
    -- Status workflow
    status ENUM('received', 'pending_review', 'approved', 'rejected', 'paid', 'archived') DEFAULT 'received',
    
    -- Praćenje korisnika i vremena
    received_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED NULL,
    rejected_at TIMESTAMP NULL,
    rejected_by BIGINT UNSIGNED NULL,
    paid_at TIMESTAMP NULL,
    archived_at TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_supplier_oib (supplier_oib),
    INDEX idx_status (status),
    INDEX idx_issue_date (issue_date),
    INDEX idx_due_date (due_date),
    
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL
);
```

**Status Workflow:**
```
received → pending_review → approved → paid → archived
                         ↘ rejected
```

### 3. incoming_invoice_items

Stavke primljenih računa.

```sql
CREATE TABLE incoming_invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incoming_invoice_id BIGINT UNSIGNED NOT NULL,
    
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) DEFAULT 'kom',
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 25.00,
    kpd_code VARCHAR(10) NULL,              -- KPD 2025 klasifikacija
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_incoming_invoice (incoming_invoice_id),
    
    FOREIGN KEY (incoming_invoice_id) REFERENCES incoming_invoices(id) ON DELETE CASCADE
);
```

## Enum Tipovi

### EracunStatus (Lokalni status)
```php
enum EracunStatus: string {
    case PENDING = 'pending';        // Čeka slanje
    case SENDING = 'sending';        // U procesu slanja
    case SENT = 'sent';             // Uspješno poslano
    case ACCEPTED = 'accepted';      // Prihvaćeno
    case REJECTED = 'rejected';      // Odbijeno
    case FAILED = 'failed';         // Neuspješno
}
```

### FinaStatus (FINA status - polling)
```php
enum FinaStatus: string {
    case RECEIVED = 'RECEIVED';                      // Primljeno
    case RECEIVING_CONFIRMED = 'RECEIVING_CONFIRMED'; // Potvrđen prijem
    case APPROVED = 'APPROVED';                      // Odobreno
    case REJECTED = 'REJECTED';                      // Odbijeno
    case PAYMENT_RECEIVED = 'PAYMENT_RECEIVED';      // Plaćanje primljeno
}
```

### IncomingInvoiceStatus (Workflow za ulazne račune)
```php
enum IncomingInvoiceStatus: string {
    case RECEIVED = 'received';            // Primljeno
    case PENDING_REVIEW = 'pending_review'; // Čeka pregled
    case APPROVED = 'approved';            // Odobreno
    case REJECTED = 'rejected';            // Odbijeno
    case PAID = 'paid';                   // Plaćeno
    case ARCHIVED = 'archived';           // Arhivirano
}
```

## Relacije Između Modela

### Invoice (Postojeći model)
```php
// Jedan Invoice može imati više EracunLog zapisa
public function eracunLogs(): HasMany

// Posljednji log za ovaj račun
public function latestEracunLog(): HasOne
```

### EracunLog
```php
// Pripada Invoice (za outgoing)
public function invoice(): BelongsTo

// Pripada IncomingInvoice (za incoming)
public function incomingInvoice(): BelongsTo
```

### IncomingInvoice
```php
// Ima mnogo stavki
public function items(): HasMany

// Ima jedan log zapis
public function eracunLog(): HasOne

// Korisnici koji su procesirali račun
public function reviewedBy(): BelongsTo
public function approvedBy(): BelongsTo
public function rejectedBy(): BelongsTo
```

### IncomingInvoiceItem
```php
// Pripada IncomingInvoice
public function incomingInvoice(): BelongsTo
```

## Primjeri Korištenja

### 1. Logiranje Slanja Izlaznog Računa

```php
use App\Models\Invoice;
use App\Models\EracunLog;
use App\Enums\EracunStatus;

$invoice = Invoice::find(1);

$log = EracunLog::create([
    'invoice_id' => $invoice->id,
    'direction' => 'outgoing',
    'message_id' => 'MSG-' . Str::uuid(),
    'ubl_xml' => $ublXml,
    'request_xml' => $soapRequest,
    'status' => EracunStatus::SENDING,
    'sent_at' => now(),
]);

// Nakon uspješnog slanja
$log->update([
    'status' => EracunStatus::SENT,
    'response_xml' => $soapResponse,
    'fina_invoice_id' => $finaId,
]);
```

### 2. Primanje Ulaznog Računa

```php
use App\Models\IncomingInvoice;
use App\Models\EracunLog;
use App\Enums\IncomingInvoiceStatus;

// Kreiraj ulazni račun
$invoice = IncomingInvoice::create([
    'supplier_oib' => '12345678901',
    'supplier_name' => 'Dobavljač d.o.o.',
    'invoice_number' => '2024-001',
    'fina_invoice_id' => 'FI-' . Str::uuid(),
    'issue_date' => now(),
    'due_date' => now()->addDays(14),
    'payment_method' => 'TRANSFER',
    'subtotal' => 1000.00,
    'tax_total' => 250.00,
    'total_amount' => 1250.00,
    'currency' => 'EUR',
    'ubl_xml' => $ublXml,
    'status' => IncomingInvoiceStatus::RECEIVED,
    'received_at' => now(),
]);

// Dodaj stavke
$invoice->items()->create([
    'name' => 'Konzultantske usluge',
    'quantity' => 10,
    'unit' => 'sat',
    'price' => 100.00,
    'total' => 1000.00,
    'tax_rate' => 25.00,
    'kpd_code' => '70220000-7',
]);

// Logiranje primanja
EracunLog::create([
    'incoming_invoice_id' => $invoice->id,
    'direction' => 'incoming',
    'message_id' => 'MSG-' . Str::uuid(),
    'fina_invoice_id' => $invoice->fina_invoice_id,
    'ubl_xml' => $ublXml,
    'response_xml' => $soapResponse,
    'status' => EracunStatus::ACCEPTED,
]);
```

### 3. Workflow za Ulazni Račun

```php
$invoice = IncomingInvoice::find(1);
$userId = auth()->id();

// Odobri račun
if ($invoice->approve($userId)) {
    // Poslano na plaćanje
}

// Odbij račun
if ($invoice->reject($userId, 'Neispravan iznos')) {
    // Račun odbijen
}

// Označi kao plaćeno
if ($invoice->markAsPaid()) {
    // Račun plaćen
}

// Arhiviraj
if ($invoice->archive()) {
    // Račun arhiviran
}
```

### 4. Query Primjeri

```php
// Svi neuspješni logovi koji mogu retry
EracunLog::pendingRetry()->get();

// Ulazni računi koji čekaju pregled
IncomingInvoice::pendingReview()->get();

// Izlazni računi s najnovijim statusom
Invoice::with('latestEracunLog')->get();

// Računi koji kasne s plaćanjem
IncomingInvoice::approved()
    ->where('due_date', '<', now())
    ->get()
    ->filter(fn($inv) => $inv->isOverdue());

// Računi određenog dobavljača
IncomingInvoice::where('supplier_oib', '12345678901')
    ->with('items')
    ->get();
```

## Migracije

Redoslijed migracija je bitan:
1. `2026_02_18_082436_create_incoming_invoices_table.php`
2. `2026_02_18_082437_create_incoming_invoice_items_table.php`
3. `2026_02_18_082438_create_eracun_logs_table.php`

Za pokretanje:
```bash
php artisan migrate
```

Za rollback:
```bash
php artisan migrate:rollback --step=3
```

## Factory za Testiranje

```php
// Kreiraj ulazni račun s stavkama
$invoice = IncomingInvoice::factory()
    ->has(IncomingInvoiceItem::factory()->count(3), 'items')
    ->create();

// Kreiraj odobren račun
$approved = IncomingInvoice::factory()->approved()->create();

// Kreiraj račun koji kasni
$overdue = IncomingInvoice::factory()->overdue()->create();
```

## Napomene

1. **Dual Status Tracking**: Sustav prati dva statusa - lokalni (`status`) i FINA (`fina_status`)
2. **Retry Mehanizam**: Neuspješna slanja se mogu automatski pokušati ponovno (max 3 puta)
3. **XML Storage**: Svi XMLovi se čuvaju za audit trail (UBL, SOAP request/response)
4. **User Tracking**: Svaka akcija na ulaznim računima prati koji korisnik je izvršio akciju
5. **Workflow Validation**: IncomingInvoiceStatus enum koristi `canTransitionTo()` za validaciju prijelaza statusa
6. **No KPR Automation**: Sustav NE automatski kreira KPR zapise za ulazne račune
7. **No Invoice Offsetting**: Sustav NE podržava prebijanje računa

## Sigurnosne Smjernice

- Svi računi moraju biti digitalno potpisani prije slanja na FINA
- UBL XML mora biti validan prema EN 16931 standardu s HR ekstenzijom
- Koristi separate e-Račun certifikat (NE fiskalizacijski certifikat)
- Validiraj OIB dobavljača pri primanju računa
- Log svih transakcija za compliance i audit

## Troubleshooting

**Problem**: Migracija pada zbog foreign key constraint  
**Rješenje**: Provjeri redoslijed migracija - incoming_invoices mora prije eracun_logs

**Problem**: Tablica već postoji ali migracija nije zabilježena  
**Rješenje**: `DROP TABLE IF EXISTS table_name` pa ponovni migrate

**Problem**: Enum vrijednosti nisu ispravne  
**Rješenje**: Provjeri da koristiš Enum case (npr. `EracunStatus::PENDING`) umjesto stringa
