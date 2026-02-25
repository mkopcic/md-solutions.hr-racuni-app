# SQLite Compatibility Check - Tvoja Aplikacija

**Brza analiza postojećih migracija za SQLite kompatibilnost**

---

## ✅ Instant Test - Pokreni Ovo Za Provjeru

```bash
# 1. Backup trenutne .env
cp .env .env.mysql.backup

# 2. Promijeni u SQLite
cat >> .env << 'EOF'

# SQLite Test Configuration
DB_CONNECTION=sqlite
DB_DATABASE=database/test_mobile.sqlite
EOF

# 3. Kreiraj test bazu
touch database/test_mobile.sqlite

# 4. Pokreni migracije
php artisan migrate:fresh --seed

# 5. Ako sve prođe - SUPER! Ako ne, vidi ispod...
```

---

## 🔍 Što Provjeriti u Tvojim Migracijama

### 1. Foreign Key Constraints

**Provjeri svaku migraciju:**
```php
// Trebaš dodati na početak svake migracije koja koristi FK:
public function up(): void 
{
    Schema::enableForeignKeyConstraints(); // ⚠️ Dodaj ovo!
    
    Schema::create('invoice_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invoice_id')
            ->constrained()
            ->onDelete('cascade');
        // ...
    });
}
```

**Ili globalno u config:**
```php
// config/database.php
'sqlite' => [
    'driver' => 'sqlite',
    // ...
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true), // ✅ Dodaj
],
```

### 2. ENUM Columns

**Prije (MySQL):**
```php
$table->enum('status', ['draft', 'pending', 'paid', 'cancelled']);
```

**Nakon (SQLite):**
```php
$table->string('status', 50)->default('draft');

// Opciono: dodaj constraint u database
DB::statement('
    CREATE TRIGGER validate_invoice_status
    BEFORE INSERT ON invoices
    FOR EACH ROW
    WHEN NEW.status NOT IN ("draft", "pending", "paid", "cancelled")
    BEGIN
        SELECT RAISE(ABORT, "Invalid status value");
    END
');

// Ili jednostavno validiraj u Model:
// app/Models/Invoice.php
protected $fillable = ['status', ...];

public function setStatusAttribute($value)
{
    $allowed = ['draft', 'pending', 'paid', 'cancelled'];
    if (!in_array($value, $allowed)) {
        throw new \InvalidArgumentException("Invalid status: {$value}");
    }
    $this->attributes['status'] = $value;
}
```

### 3. Date/Time Funkcije

**MySQL funkcije koje ne rade:**
```php
// ❌ Ne radi u SQLite
DB::table('invoices')
    ->select(DB::raw('YEAR(created_at) as year'))
    ->groupBy('year')
    ->get();

// ✅ SQLite verzija
DB::table('invoices')
    ->select(DB::raw('strftime("%Y", created_at) as year'))
    ->groupBy('year')
    ->get();
```

**Kreiranje helper-a:**
```php
// app/Helpers/DatabaseHelper.php
class DatabaseHelper 
{
    public static function yearColumn(string $column): string 
    {
        return config('database.default') === 'sqlite'
            ? "strftime('%Y', {$column})"
            : "YEAR({$column})";
    }
    
    public static function monthColumn(string $column): string 
    {
        return config('database.default') === 'sqlite'
            ? "strftime('%m', {$column})"
            : "MONTH({$column})";
    }
}

// Korištenje:
DB::table('invoices')
    ->select(DB::raw(DatabaseHelper::yearColumn('created_at') . ' as year'))
    ->groupBy('year')
    ->get();
```

### 4. JSON Columns

**Provjeri JSON podrška:**
```php
// ✅ Ovo obično radi (SQLite 3.38+)
$table->json('metadata');

// Test JSON queries:
Invoice::whereJsonContains('metadata->tags', 'urgent')->get();
```

**Ako ne radi, alternativa:**
```php
// Koristi TEXT i manual encode/decode
$table->text('metadata');

// U modelu:
protected $casts = [
    'metadata' => 'array',  // Laravel automatski encode/decode
];
```

### 5. Column Type Mapping

| MySQL Type | SQLite Equivalent | Napomena |
|-----------|-------------------|----------|
| `TINYINT` | `INTEGER` | ✅ Auto convert |
| `SMALLINT` | `INTEGER` | ✅ Auto convert |
| `INT` | `INTEGER` | ✅ Auto convert |
| `BIGINT` | `INTEGER` | ✅ Auto convert |
| `VARCHAR` | `TEXT` | ✅ Auto convert |
| `TEXT` | `TEXT` | ✅ Isti |
| `DECIMAL` | `REAL` | ⚠️ Može biti loss of precision |
| `DOUBLE` | `REAL` | ✅ OK |
| `DATE` | `TEXT` | ✅ Laravel handle-a |
| `DATETIME` | `TEXT` | ✅ Laravel handle-a |
| `TIMESTAMP` | `TEXT` | ✅ Laravel handle-a |
| `BLOB` | `BLOB` | ✅ Isti |
| `ENUM` | ❌ `TEXT` | ⚠️ Manual validation |

### 6. Auto Increment

```php
// ✅ Radi identično
$table->id(); // Auto-increment primary key
```

### 7. Indexes

```php
// ✅ Rade
$table->index('email');
$table->unique('invoice_number');
$table->index(['user_id', 'status']); // Compound index
```

---

## 🔧 Provjera Postojećih Migracija

### Ručna Provjera

```bash
# Lista svih migracija
ls -la database/migrations/

# Provjeri svaku:
# 1. Has ENUM? → Promijeni u string + validation
# 2. Has MySQL date functions? → Promijeni u SQLite verziju  
# 3. Has foreign keys? → Enable constraints
# 4. Altering columns? → Rebuild table (SQLite limitation)
```

### Automated Check Script

```php
// database/scripts/check_sqlite_compatibility.php
<?php

$migrationsPath = database_path('migrations');
$files = glob($migrationsPath . '/*.php');

$issues = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check for ENUM
    if (preg_match('/->enum\(/', $content)) {
        $issues[] = basename($file) . ": Contains ENUM column";
    }
    
    // Check for MySQL date functions
    if (preg_match('/(YEAR|MONTH|DAY|DATE_FORMAT)\(/', $content)) {
        $issues[] = basename($file) . ": Contains MySQL date function";
    }
    
    // Check for table alterations
    if (preg_match('/Schema::table\(/', $content)) {
        $issues[] = basename($file) . ": Alters table (may be problematic)";
    }
}

if (empty($issues)) {
    echo "✅ All migrations look SQLite compatible!\n";
} else {
    echo "⚠️ Found potential issues:\n\n";
    foreach ($issues as $issue) {
        echo "  - {$issue}\n";
    }
}
```

**Pokreni:**
```bash
php database/scripts/check_sqlite_compatibility.php
```

---

## 🧪 Test Scenariji

### Test #1: Fresh Migration
```bash
DB_CONNECTION=sqlite php artisan migrate:fresh
# Očekuješ: 0 errora
```

### Test #2: Migration + Seeding
```bash
DB_CONNECTION=sqlite php artisan migrate:fresh --seed
# Očekuješ: Sve tablice + seed data
```

### Test #3: Application Flow
```bash
# Pokreni app
DB_CONNECTION=sqlite php artisan serve

# Testiraj:
# 1. Registracija/Login
# 2. Kreiranje novog računa
# 3. Dodavanje stavki
# 4. PDF generiranje
# 5. Pregled računa
# 6. Izmjena računa
# 7. Brisanje računa
```

### Test #4: Relations & Queries
```php
// Test u tinker
php artisan tinker

>>> use App\Models\Invoice;
>>> $invoice = Invoice::with('items', 'client')->first();
>>> $invoice->items->count();
>>> Invoice::where('total', '>', 1000)->get();
```

### Test #5: Foreign Keys
```php
// Provjeri cascade delete
$invoice = Invoice::factory()->create();
$item = $invoice->items()->create([...]);

$invoice->delete();

// Item bi trebao biti obrisan ako FK constraint radi
$item->fresh(); // Should be null
```

---

## 🐛 Česti Problemi i Rješenja

### Problem 1: "FOREIGN KEY constraint failed"

**Razlog:** Foreign keys nisu omogućeni

**Rješenje:**
```php
// config/database.php
'sqlite' => [
    'foreign_key_constraints' => true, // ← Dodaj ovo
],
```

### Problem 2: "no such function: YEAR"

**Razlog:** MySQL funkcija u SQLite-u

**Rješenje:**
```php
// Zamijeni
YEAR(created_at) → strftime('%Y', created_at)
MONTH(created_at) → strftime('%m', created_at)
DATE(created_at) → date(created_at)
```

### Problem 3: "Cannot add a STORED GENERATED column"

**Razlog:** SQLite ima ograničenja za generated columns

**Rješenje:**
```php
// Nemoj koristiti stored generated columns
// Koristi virtual ili compute u aplikaciji
```

### Problem 4: "Cannot drop column"

**Razlog:** SQLite ne podržava DROP COLUMN (prije verzije 3.35)

**Rješenje:**
```php
// Umjesto:
$table->dropColumn('old_column');

// Ručno rebuild table:
Schema::table('invoices', function (Blueprint $table) {
    // Workaround: ostaviti column ali ignorirati
    // Ili kreirati novu tablicu, kopirati data, dropati staru, renamati novu
});
```

### Problem 5: Decimal Precision Loss

**Razlog:** SQLite koristi REAL za decimale

**Rješenje:**
```php
// Ako radiš s novcem, koristi integer (cents):
$table->integer('total_cents'); // Umjesto decimal('total', 10, 2)

// U model:
protected $casts = [
    'total_cents' => 'integer',
];

public function getTotalAttribute(): float 
{
    return $this->total_cents / 100;
}

public function setTotalAttribute(float $value): void 
{
    $this->attributes['total_cents'] = (int) ($value * 100);
}
```

---

## ✅ Checklist Prije Go-Live

### Migracije
- [ ] Sve migracije rade na SQLite-u
- [ ] Foreign keys su omogućeni i testirati
- [ ] ENUM columns zamijenjeni stringovima
- [ ] Date funkcije prilagođene
- [ ] Seed data se upisuje ispravno

### Modeli
- [ ] Relations rade
- [ ] Accessors/Mutators rade
- [ ] Casts rade (posebno JSON i decimali)
- [ ] Scopes rade

### Queries
- [ ] Complex queries testirane
- [ ] Aggregates (SUM, AVG, COUNT) rade
- [ ] JOINs rade
- [ ] Subqueries rade
- [ ] Raw queries prilagođeni

### Performance
- [ ] Indexi kreirani na često pretraženim kolonama
- [ ] N+1 queries riješeni (eager loading)
- [ ] Database size testiran s realističnim datasetom

---

## 📊 Performance Usporedba

### Očekivane Performanse SQLite vs. MySQL:

| Operacija | MySQL (Remote) | SQLite (Local) |
|-----------|---------------|----------------|
| Read | ~50-100ms | ~1-5ms | 
| Write | ~100-200ms | ~5-10ms |
| Complex Query | ~200-500ms | ~10-50ms |

**SQLite je 10-50x brži za lokalne operacije!**

---

## 🚀 Quick Migration Script

```bash
#!/bin/bash
# migrate_to_sqlite.sh

echo "🔄 Migrating to SQLite..."

# Backup
cp .env .env.backup
echo "✅ Backed up .env"

# Change to SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=database\/mobile.sqlite/' .env
echo "✅ Updated .env"

# Create database
touch database/mobile.sqlite
echo "✅ Created SQLite database"

# Run migrations
php artisan migrate:fresh --seed
echo "✅ Ran migrations"

# Test
php artisan tinker --execute="echo 'Invoices: ' . App\\Models\\Invoice::count();"
echo "✅ Test query executed"

echo "🎉 Migration complete!"
echo "⚠️  Test thoroughly before deleting .env.backup"
```

---

**Status:** Spremno za testing  
**Sljedeći korak:** Pokreni SQLite test i dokumentiraj probleme

*Kreirao: 26/02/2026*
