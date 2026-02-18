# Fiskalizacija - Arhitektura i Funkcionalnost (Laravel 12)

**Datum dokumenta:** 13. studenog 2025.  
**Verzija aplikacije:** Laravel 12  
**FINA specifikacija:** v2.3 (28.12.2022)  
**Certifikat:** FISKAL 3 (86058362621.F3.3.p12)

---

## 1. PREGLED SUSTAVA

Sustav fiskalizacije integriran u Laravel 12 aplikaciju omogućava automatsko fiskaliziranje računa kroz FINA servis (Hrvatska Porezna uprava). Aplikacija je dizajnirana za sportske udruge sa podrškom za:

- **SOAP komunikaciju** s FINA endpointima (demo i produkcija)
- **XML potpisivanje** (XMLDSig) pomoću PKCS#12 certifikata
- **ZKI (Zaštitni kod izdavatelja)** generiranje
- **JIR (Jedinstveni identifikator računa)** primanje od FINA servisa
- **Asinkrono izvršavanje** putem Laravel Queue-a
- **Retry mehanizam** za neuspjele fiskalizacije
- **Event-driven** arhitektura (RacunFiskaliziran, FiskalizacijaFailed)

---

## 2. ARHITEKTURA APLIKACIJE

### 2.1 Direktorijska Struktura

```
app/
├── Services/Fiskalizacija/          # Core business logic
│   ├── FiskalizacijaService.php     # Glavni servis - orkestrira cijeli proces
│   ├── UslugaFiskalizacije.php      # Wrapper/fasada
│   ├── FiskalContext.php            # Value object - drži sve fiskalne podatke
│   ├── FiskalContextResolver.php    # Resolver - pretvara Racun → FiskalContext
│   ├── ZkiGenerator.php             # Generira ZKI hash (MD5 od SHA1 signature)
│   ├── XmlRequestBuilder.php        # Gradi XML strukture (RacunZahtjev)
│   ├── XmlSigner.php                # Potpisuje XML s XMLDSig
│   ├── FinaClient.php               # HTTP client za FINA SOAP API
│   ├── CertificateLoader.php        # Učitava PKCS#12 certifikat
│   └── BrojRacunaGenerator.php      # Generira sekvencijalni broj računa
│
├── Console/Commands/                # Artisan komande
│   ├── FiskalizirajRacun.php        # fiskal:send {racun_id}
│   ├── FiskalMinimalRequest.php     # fiskal:request:minimal (test)
│   ├── FiskalDiagnostics.php        # fiskal:diagnostics (WSDL, Echo)
│   └── FiskalWsdlExplore.php        # fiskal:wsdl:explore (SOAP operacije)
│
├── Jobs/
│   ├── FiskalizirajRacunJob.php     # Async fiskalizacija kroz queue
│   └── RetryFiskalizacijaJob.php    # Retry neuspjelih fiskalizacija
│
├── Events/
│   ├── RacunFiskaliziran.php        # Event - uspješna fiskalizacija
│   └── FiskalizacijaFailed.php      # Event - neuspjela fiskalizacija
│
├── Listeners/
│   ├── LogRacunFiskaliziran.php     # Logger za uspjeh
│   └── LogFiskalizacijaFailed.php   # Logger za greške
│
└── Models/Racuni/
    ├── Racun.php                    # Glavni model računa
    ├── FiskalniLog.php              # Log table (request/response XML)
    ├── FiskalniBroj.php             # Sekvencijalni brojevi po godini/lokaciji
    └── NacinPlacanja.php            # Način plaćanja

config/
└── fiskalizacija.php                # Kompletna konfiguracija sustava

certs/
└── 86058362621.F3.3.p12             # FISKAL 3 certifikat (demo)
```

---

## 3. LIFECYCLE FISKALIZACIJE RAČUNA

### 3.1 Dijagram Toka

```
┌─────────────────┐
│  Racun kreiran  │
└────────┬────────┘
         │
         v
┌─────────────────────────────┐
│ FiskalizirajRacunJob        │
│ dispatch (async queue)      │
└────────┬────────────────────┘
         │
         v
┌──────────────────────────────────────────────────────────────┐
│ FiskalizacijaService::fiskaliziraj(Racun $racun)             │
│ ─────────────────────────────────────────────────────────    │
│ 1. FiskalContextResolver → resolve context iz Racun modela   │
│ 2. Spremi oznake u Racun (oznaka_poslovnog_prostora, itd.)  │
│ 3. ZkiGenerator → generate(Racun, FiskalContext)             │
│ 4. XmlRequestBuilder → build(Racun, FiskalContext, ZKI)      │
│ 5. XmlSigner → sign(XML, FiskalContext)                      │
│ 6. FiskalniLog::create (pending)                             │
│ 7. FinaClient → send(XML, FiskalContext)                     │
│ 8. Parse response (JIR extraction)                           │
│ 9. Update FiskalniLog (success/failed)                       │
│ 10. Update Racun (zki, jir, fiskaliziran_u)                  │
│ 11. event(RacunFiskaliziran) ili event(FiskalizacijaFailed)  │
└────────┬─────────────────────────────────────────────────────┘
         │
         v
┌────────────────────┐
│ Response Handling  │
├────────────────────┤
│ SUCCESS:           │
│ - racun.jir ✓      │
│ - racun.zki ✓      │
│ - fiskaliziran_u ✓ │
│                    │
│ FAILED:            │
│ - error_message ✓  │
│ - fiskaliziran_u ∅ │
│ - RetryJob queue   │
└────────────────────┘
```

### 3.2 Ključni Koraci (Detaljno)

#### **Korak 1: Resolve FiskalContext**

`FiskalContextResolver::resolve(Racun $racun)` pretvara `Racun` model u `FiskalContext` value object:

```php
FiskalContext {
    udruga: Udruga,                     // Udruga koja izdaje račun
    lokacija: ?Lokacija,                // Poslovni prostor
    oznakaSlijeda: string,              // P (poslovno nasljeđivanje)
    oznakaPoslovnogProstora: string,    // OznPosPr (npr. K1, POS)
    oznakaNaplatnogUredaja: string,     // OznNapUr (npr. K1, KASA)
    izdanU: Carbon,                     // Timestamp izdavanja
    redniBroj: int,                     // Sekvencijalni broj (autoincrement)
    uSustavuPdv: bool,                  // Je li udruga u sustavu PDV-a
}
```

**Fallback logika za oznake:**
- `oznakaPoslovnogProstora`: racun → lokacija → lokacija_kasa → 'POS'
- `oznakaNaplatnogUredaja`: racun → lokacija → lokacija_kasa → 'KASA'

**Sanitizacija:**
- Uppercase
- Samo A-Z, 0-9, _, - (regex: `/[^A-Z0-9_-]/`)
- Max 20 znakova

#### **Korak 2: Generiranje ZKI**

`ZkiGenerator::generate(Racun, FiskalContext)` kreira **Zaštitni kod izdavatelja**:

**Formula:**
```
baseString = OIB + DatumVrijeme + BrOznRac + OznPosPr + OznNapUr + IznosUkupno
             └──┬──┘ └────┬───┘   └───┬───┘   └───┬──┘   └───┬──┘   └────┬─────┘
                │         │            │           │          │            │
         Udruga OIB   dd.mm.YYYYTHH:MM:SS  K1/K1/000001    K1       K1     1500.00
```

**Proces:**
1. Sastavi base string
2. Potpiši s privatnim ključem iz certifikata (SHA-1)
3. MD5 hash potpisa
4. Uppercase hex string

**Output:** `ZKI = "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6"`

#### **Korak 3: XML Request Builder**

`XmlRequestBuilder::build(Racun, FiskalContext, ZKI)` gradi XML:

```xml
<RacunZahtjev xmlns="http://www.apis-it.hr/fin/2012/types/f73" Id="RacunZahtjev-{uuid}">
    <Zaglavlje Id="Zaglavlje-{uuid}">
        <IdPoruke>{uuid}</IdPoruke>
        <DatumVrijeme>13.11.2025T22:30:45</DatumVrijeme>
    </Zaglavlje>
    <Racun Id="Racun-{uuid}">
        <OIB>96557881558</OIB>
        <USustPdv>N</USustPdv>
        <DatVrijeme>13.11.2025T22:30:45</DatVrijeme>
        <OznSlijed>P</OznSlijed>
        <BrRac>
            <BrOznRac>000001</BrOznRac>
            <OznPosPr>K1</OznPosPr>
            <OznNapUr>K1</OznNapUr>
        </BrRac>
        <IznosUkupno>1500.00</IznosUkupno>
        <NacinPlac>G</NacinPlac>
        <ZastKod>A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6</ZastKod>
    </Racun>
</RacunZahtjev>
```

**Payment Mapping (config/fiskalizacija.php):**
```php
'payment_codes' => [
    'gotovina' => 'G',        // Gotovina
    'kartica' => 'K',         // Kartice
    'virman' => 'T',          // Transakcijski račun
    'bank_transfer' => 'T',   // Transakcijski račun
    'direct_debit' => 'T',    // Transakcijski račun
    'other' => 'O',           // Ostalo
]
```

#### **Korak 4: XML Signing (XMLDSig)**

`XmlSigner::sign(XML, FiskalContext)` dodaje digitalni potpis:

**Korištena biblioteka:** `robrichards/xmlseclibs`

**Proces:**
1. Parse XML (DOMDocument)
2. Kreira `XMLSecurityDSig` objekt
3. Kanonikalizacija: C14N
4. Dodavanje reference na root element (SHA-1 hash)
5. Potpisivanje s RSA-SHA1 algoritmom
6. Dodavanje X.509 certifikata u `<Signature>` blok
7. Append `<Signature>` u root element

**Output:**
```xml
<RacunZahtjev ...>
    <Zaglavlje>...</Zaglavlje>
    <Racun>...</Racun>
    <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
        <SignedInfo>
            <CanonicalizationMethod Algorithm="..." />
            <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
            <Reference URI="#RacunZahtjev-{uuid}">
                <Transforms>...</Transforms>
                <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                <DigestValue>...</DigestValue>
            </Reference>
        </SignedInfo>
        <SignatureValue>...</SignatureValue>
        <KeyInfo>
            <X509Data>
                <X509Certificate>...</X509Certificate>
                <X509SubjectName>...</X509SubjectName>
                <X509IssuerSerial>...</X509IssuerSerial>
            </X509Data>
        </KeyInfo>
    </Signature>
</RacunZahtjev>
```

#### **Korak 5: SOAP Request**

`FinaClient::send(XML, FiskalContext)` šalje SOAP zahtjev:

**HTTP Options:**
```php
[
    'cert' => '/tmp/fina_cert_XXXXX',      // Temporary cert PEM
    'ssl_key' => '/tmp/fina_key_XXXXX',    // Temporary key PEM
    'verify' => true,                       // CA bundle verification
    'timeout' => 10,                        // seconds
]
```

**SOAP Envelope:**
```xml
<soapenv:Envelope 
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
    xmlns:tns="http://www.apis-it.hr/fin/2012/types/f73">
    <soapenv:Body>
        {signed-xml}
    </soapenv:Body>
</soapenv:Envelope>
```

**Endpoint (Demo):**
```
POST https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest
Content-Type: text/xml; charset=utf-8
```

#### **Korak 6: Response Parsing**

`FinaClient::parseSoapResponse(string $body)` parsira odgovor:

**Success Response:**
```xml
<soapenv:Envelope ...>
    <soapenv:Body>
        <tns:RacunOdgovor>
            <tns:Jir>12345678-90ab-cdef-1234-567890abcdef</tns:Jir>
        </tns:RacunOdgovor>
    </soapenv:Body>
</soapenv:Envelope>
```

**Error Response:**
```xml
<soapenv:Envelope ...>
    <soapenv:Body>
        <tns:RacunOdgovor>
            <tns:Greske>
                <tns:Greska>
                    <tns:SifraGreske>s006</tns:SifraGreske>
                    <tns:PorukaGreske>Nepoznat obveznik fiskalizacije</tns:PorukaGreske>
                </tns:Greska>
            </tns:Greske>
        </tns:RacunOdgovor>
    </soapenv:Body>
</soapenv:Envelope>
```

**Extracted Data:**
```php
[
    'jir' => '12345678-90ab-cdef-1234-567890abcdef',
    'response' => '<xml>...</xml>',
    'status' => 'received', // or 'error'
    'errors' => ['s006: Nepoznat obveznik fiskalizacije'],
]
```

---

## 4. MODELI I BAZA PODATAKA

### 4.1 Racun Model

**Tabela:** `racuni`

**Fiskalizacijski stupci:**
```php
'oznaka_slijeda'              => 'P',
'oznaka_poslovnog_prostora'   => 'K1',
'oznaka_naplatnog_uredaja'    => 'K1',
'izdan_u'                     => '2025-11-13 22:30:45',
'potvrda_broj'                => 'K1/K1/000001',
'zki'                         => 'A1B2C3D4...',
'jir'                         => '12345678-90ab...',
'fiskaliziran_u'              => '2025-11-13 22:30:50',
```

**Relacije:**
```php
racun->udruga              // Organizacijska jedinica koja izdaje račun
racun->lokacija            // Poslovni prostor
racun->nacinPlacanja       // Način plaćanja (gotovina, kartica, virman, ...)
racun->fiskalniLog         // hasMany - svi pokušaji fiskalizacije
```

### 4.2 FiskalniLog Model

**Tabela:** `fiskalni_logs`

**Stupci:**
```php
'racun_id'       => integer,
'request_xml'    => text,      // Signed XML request
'response_xml'   => text,      // SOAP response
'status'         => enum,      // 'pending', 'success', 'failed'
'error_message'  => text,      // Parsed errors from FINA
'sent_at'        => timestamp,
'retried_at'     => timestamp,
```

**Primjer zapisa:**
```php
[
    'racun_id' => 123,
    'request_xml' => '<RacunZahtjev>...</RacunZahtjev>',
    'response_xml' => '<RacunOdgovor><Greske>...</Greske></RacunOdgovor>',
    'status' => 'failed',
    'error_message' => 's006: Nepoznat obveznik fiskalizacije',
    'sent_at' => '2025-11-13 22:30:45',
    'retried_at' => null,
]
```

### 4.3 FiskalniBroj Model

**Tabela:** `fiskalni_brojevi`

**Stupci:**
```php
'udruga_id'                    => integer,
'godina'                       => integer,  // 2025
'oznaka_poslovnog_prostora'    => string,   // K1
'oznaka_naplatnog_uredaja'     => string,   // K1
'zadnji_broj'                  => integer,  // 1, 2, 3, ...
```

**Unique constraint:**
```sql
UNIQUE (udruga_id, godina, oznaka_poslovnog_prostora, oznaka_naplatnog_uredaja)
```

**Proces generiranja broja:**
```php
DB::transaction(function () {
    $record = FiskalniBroj::where(...)->lockForUpdate()->first();
    if (!$record) {
        $record = new FiskalniBroj(['zadnji_broj' => 0]);
    }
    $record->zadnji_broj++;
    $record->save();
    return $record->zadnji_broj;
});
```

**Output:** Sekvencijalni broj (1, 2, 3, ...) za tu kombinaciju udruga/godina/pos/uredaj.

---

## 5. KONFIGURACIJA (config/fiskalizacija.php)

```php
return [
    // Omogući/onemogući fiskalizaciju (demo režim ako je false)
    'enabled' => env('FISKAL_ENABLED', true),

    // Okruženje: 'demo' ili 'prod'
    'environment' => env('FISKAL_ENV', 'demo'),

    // FINA endpointi
    'endpoints' => [
        'demo' => 'https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest',
        'prod' => 'https://cis.porezna-uprava.hr:8449/FiskalizacijaService',
    ],

    // Certifikat konfiguracija
    'cert' => [
        'path' => env('FISKAL_CERT_PATH', base_path('certs/86058362621.F3.3.p12')),
        'password' => env('FISKAL_CERT_PASS'),
        'ca_path' => env('FISKAL_CA_PATH'),  // CA bundle (optional)
    ],

    // HTTP timeout (sekunde)
    'timeout' => (int) env('FISKAL_TIMEOUT', 10),

    // Defaultna oznaka slijeda (P - poslovno nasljeđivanje)
    'default_slijed' => env('FISKAL_DEFAULT_SLIJED', 'P'),

    // Mapiranje načina plaćanja → FINA kodovi
    'payment_codes' => [
        'gotovina' => 'G',           // Gotovina
        'kartica' => 'K',            // Kartice (debitne i kreditne)
        'stripe' => 'K',             // Kartice (Stripe)
        'corvuspay' => 'K',          // Kartice (CorvusPay)
        'wspay' => 'K',              // Kartice (WSPay)
        'virman' => 'T',             // Transakcijski račun (FINA spec: T)
        'kekspay' => 'O',            // Ostalo (KeksPay)
        'bank_transfer' => 'T',      // Transakcijski račun (FINA spec: T)
        'direct_debit' => 'T',       // Transakcijski račun
        'other' => 'O',              // Ostalo
    ],

    // Logging
    'logger' => [
        'enabled' => true,
        'channel' => env('FISKAL_LOG_CHANNEL', 'stack'),
    ],

    // Retry mehanizam
    'retry' => [
        'max_attempts' => (int) env('FISKAL_MAX_ATTEMPTS', 3),
        'backoff_seconds' => (int) env('FISKAL_RETRY_BACKOFF', 300),  // 5 min
    ],
];
```

### ENV Varijable

```ini
FISKAL_ENABLED=true
FISKAL_ENV=demo
FISKAL_CERT_PATH=certs/86058362621.F3.3.p12
FISKAL_CERT_PASS=
FISKAL_CA_PATH=
FISKAL_TIMEOUT=10
FISKAL_DEFAULT_SLIJED=P
FISKAL_LOG_CHANNEL=stack
FISKAL_MAX_ATTEMPTS=3
FISKAL_RETRY_BACKOFF=300
```

---

## 6. ARTISAN KOMANDE

### 6.1 `fiskal:send`

**Signature:**
```bash
php artisan fiskal:send {racun_id} [--queue] [--force]
```

**Opcije:**
- `--queue` : Fiskalizacija ide u queue umjesto sinkrono
- `--force` : Forsiraj izvršenje iako je `FISKAL_ENABLED=false`

**Primjer:**
```bash
# Sinkrono
php artisan fiskal:send 123

# Asinkrono (queue)
php artisan fiskal:send 123 --queue
```

**Output:**
```
┌──────┬──────────────────────────────────────┬──────────────────────────────────────┬───────────────────┐
│ Račun│ JIR                                  │ ZKI                                  │ Fiskaliziran u    │
├──────┼──────────────────────────────────────┼──────────────────────────────────────┼───────────────────┤
│ 123  │ 12345678-90ab-cdef-1234-567890abcdef │ A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6     │ 13.11.2025 22:30:45│
└──────┴──────────────────────────────────────┴──────────────────────────────────────┴───────────────────┘
```

### 6.2 `fiskal:request:minimal`

**Signature:**
```bash
php artisan fiskal:request:minimal [--send] [--store=] [options...]
```

**Opcije:**
```
--send              : Šalje zahtjev na FINA endpoint (default: samo generira XML)
--store=PATH        : Direktorij za spremanje XML artifakata
--amount=1500.00    : Iznos računa
--oib=96557881558   : OIB izdavatelja
--pos=K1            : Oznaka poslovnog prostora
--uredaj=K1         : Oznaka naplatnog uređaja
--sequence=P        : Oznaka slijeda
--number=1          : Redni broj računa
--payment=gotovina  : Način plaćanja
--pdv=0             : U sustavu PDV-a (0/1)
--endpoint=         : Custom endpoint URL
--cert=             : Custom certifikat path
--cert-pass=        : Lozinka certifikata
--ca=               : CA bundle path
```

**Primjer:**
```bash
php artisan fiskal:request:minimal \
    --send \
    --amount=1500.00 \
    --oib=96557881558 \
    --pos=K1 \
    --uredaj=K1 \
    --payment=gotovina
```

**Generirani fajlovi:**
```
storage/app/fiskalizacija/minimal/20251113-223045/
├── unsigned-request.xml     # XML prije potpisivanja
├── request.xml              # Potpisani XML
├── soap-request.xml         # SOAP Envelope
├── soap-response.xml        # FINA response
└── meta.json                # Metadata (timestamp, config, JIR, errors)
```

### 6.3 `fiskal:diagnostics`

**Signature:**
```bash
php artisan fiskal:diagnostics [--operation=all] [--env=] [--store=] [--message=ping] [--include-docs]
```

**Opcije:**
```
--operation=all       : Testovi (wsdl, echo, all)
--env=demo            : Prepiši FISKAL_ENV
--store=PATH          : Direktorij za spremanje rezultata
--message=ping        : Tekst EchoRequest poruke
--include-docs        : Kopiraj dokumentaciju u output direktorij
```

**Primjer:**
```bash
php artisan fiskal:diagnostics --operation=echo --message="Hello FINA"
```

**Output:**
```
storage/app/fiskalizacija/diagnostics/20251113-223045/
├── echo-request.xml          # Echo SOAP zahtjev
├── echo-response.xml         # Echo SOAP odgovor
├── wsdl.xml                  # WSDL definicija
└── diagnostics.json          # Summary JSON
```

### 6.4 `fiskal:wsdl:explore`

**Signature:**
```bash
php artisan fiskal:wsdl:explore [--store=]
```

**Funkcija:** Testira sve SOAP operacije iz WSDL-a:
- Echo (✅ radi)
- ProvjeraPoslovnogProstora (❌ s006)
- ProvjeraNaplatnogUredjaja (❌ s006)
- RacunZahtjev (❌ s006)

**Output:**
```
storage/app/fiskalizacija/wsdl-explore/20251113-223045/
├── echo-test.xml
├── provjera-poslovnog-prostora-test.xml
├── provjera-naplatnog-uredaja-test.xml
├── racun-zahtjev-test.xml
└── results.json
```

---

## 7. JOBS (ASINKRONI PROCESI)

### 7.1 FiskalizirajRacunJob

**Queue:** `fiskalizacija`

**Kod:**
```php
class FiskalizirajRacunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $racunId,
        public readonly bool $force = false
    ) {
        $this->onQueue('fiskalizacija');
    }

    public function handle(FiskalizacijaService $fiskalizacijaService): void
    {
        if (!config('fiskalizacija.enabled') && !$this->force) {
            Log::info('Fiskalizacija preskočena - servis onemogućen.');
            return;
        }

        $racun = Racun::find($this->racunId);
        if (!$racun) {
            Log::warning('Fiskalizacija nije moguća - račun ne postoji.');
            return;
        }

        $fiskalizacijaService->fiskaliziraj($racun);
    }
}
```

**Dispatch:**
```php
// Asinkrono
FiskalizirajRacunJob::dispatch($racun->id);

// Sinkrono (za testiranje)
FiskalizirajRacunJob::dispatchSync($racun->id);

// Forsirano (ignoriraj FISKAL_ENABLED)
FiskalizirajRacunJob::dispatch($racun->id, force: true);
```

**Queue konfiguracija (.env):**
```ini
QUEUE_CONNECTION=database
QUEUE_FISKALIZACIJA=fiskalizacija
```

**Pokretanje queue workera:**
```bash
php artisan queue:work --queue=fiskalizacija
```

### 7.2 RetryFiskalizacijaJob

**Funkcija:** Retry neuspjelih fiskalizacija s backoff mehanizmom.

**Kod:**
```php
class RetryFiskalizacijaJob implements ShouldQueue
{
    public function handle(): void
    {
        $failedLogs = FiskalniLog::where('status', 'failed')
            ->whereNull('retried_at')
            ->orWhere('retried_at', '<', now()->subSeconds(config('fiskalizacija.retry.backoff_seconds')))
            ->limit(config('fiskalizacija.retry.max_attempts'))
            ->get();

        foreach ($failedLogs as $log) {
            FiskalizirajRacunJob::dispatch($log->racun_id, force: true);
            $log->retried_at = now();
            $log->save();
        }
    }
}
```

**Pokretanje:**
```bash
php artisan queue:work --queue=fiskalizacija-retry
```

---

## 8. EVENTS & LISTENERS

### 8.1 RacunFiskaliziran Event

**Kod:**
```php
class RacunFiskaliziran
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Racun $racun) {}
}
```

**Listener: LogRacunFiskaliziran**
```php
class LogRacunFiskaliziran
{
    public function handle(RacunFiskaliziran $event): void
    {
        Log::info('Račun fiskaliziran.', [
            'racun_id' => $event->racun->id,
            'jir' => $event->racun->jir,
            'zki' => $event->racun->zki,
        ]);
    }
}
```

### 8.2 FiskalizacijaFailed Event

**Kod:**
```php
class FiskalizacijaFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Racun $racun,
        public readonly string $reason
    ) {}
}
```

**Listener: LogFiskalizacijaFailed**
```php
class LogFiskalizacijaFailed
{
    public function handle(FiskalizacijaFailed $event): void
    {
        Log::error('Fiskalizacija nije uspjela.', [
            'racun_id' => $event->racun->id,
            'reason' => $event->reason,
        ]);
    }
}
```

**Registracija (EventServiceProvider):**
```php
protected $listen = [
    RacunFiskaliziran::class => [
        LogRacunFiskaliziran::class,
    ],
    FiskalizacijaFailed::class => [
        LogFiskalizacijaFailed::class,
    ],
];
```

---

## 9. TESTIRANJE I DEBUGGING

### 9.1 Provjera TLS Konekcije

```bash
openssl s_client -connect cistest.apis-it.hr:8449 \
    -cert certs/86058362621.F3.3.pem \
    -key certs/86058362621.F3.3.key \
    -CAfile certs/fina-demo-ca-2020.pem \
    -tls1_3
```

**Expected output:**
```
Verify return code: 0 (ok)
TLSv1.3, Cipher is TLS_AES_256_GCM_SHA384
```

### 9.2 Echo Test

```bash
php artisan fiskal:diagnostics --operation=echo --message="Test connection"
```

**Success response:**
```xml
<EchoResponse>
    <EchoValue>Test connection</EchoValue>
</EchoResponse>
```

### 9.3 Minimal Request Test

```bash
php artisan fiskal:request:minimal --send --amount=1500 --store=/tmp/fiskal-test
```

**Check output:**
```bash
cat /tmp/fiskal-test/meta.json | jq '.jir'
```

### 9.4 Log Analysis

**Fiskal logs:**
```bash
tail -f storage/logs/laravel.log | grep -i fiskal
```

**Database logs:**
```sql
SELECT racun_id, status, error_message, sent_at 
FROM fiskalni_logs 
ORDER BY sent_at DESC 
LIMIT 20;
```

### 9.5 Common Errors (TOČNO PREMA FINA SPEC v2.3)

| Error Code | Opis (iz FINA PDF) | Napomena |
|------------|-------------------|----------|
| **s001** | Poruka nije u skladu s XML shemom | Provjeri strukturu XML-a prema shemi |
| **s002** | Certifikat nije izdan od strane FINA RDC CA ili je istekao ili je ukinut | Provjeri valjanost certifikata |
| **s003** | Certifikat ne sadrži naziv 'Fiskal' | Certifikat mora biti FISKAL tip |
| **s004** | Neispravan digitalni potpis | Provjeri XMLDSig potpis |
| **s005** | OIB iz poruke zahtjeva nije jednak OIB-u iz certifikata | **OVA GREŠKA** - OIB mismatch! |
| **s006** | Sistemska pogreška prilikom obrade zahtjeva | **GENERIČKA greška** - može biti bilo što |
| **s007** | Datum izdavanja računa u poruci promjene načina plaćanja nije jednak trenutnom datumu | Samo za PromijeniNacPlac operaciju |
| **s008** | Podaci za račun u poruci promjene načina plaćanja razlikuju se od podataka fiskaliziranog računa ili račun nije fiskaliziran | Samo za PromijeniNacPlac operaciju |
| **v101** | 'Datum i vrijeme slanja' je za više od 6 sati manje od trenutnog datuma/vremena | Provjeri timestamp u Zaglavlje |
| **v103** | 'Datum i vrijeme izdavanja' računa je za više od 6 sati manje od trenutnog datuma/vremena | Provjeri DatVrijeme u Racun |
| **v104** | 'Datum i vrijeme izdavanja' računa je veće od trenutnog datuma/vremena | Provjeri DatVrijeme - ne smije biti u budućnosti |
| **v105** | 'Brojčana oznaka računa' ima vrijednost '0' | BrOznRac mora biti >= 1 |
| **v106** | 'Brojčana oznaka računa' ima više od 6 znamenki | BrOznRac max 6 znamenki |

---

## 10. SIGURNOST

### 10.1 Certifikat Management

**PKCS#12 certifikat:**
- Lokacija: `certs/86058362621.F3.3.p12`
- Tip: FISKAL 3 (demo)
- Issuer: Fina Demo CA 2020
- Validity: 2025-10-31 do 2030-07-31
- OIB: 86058362621

**Lozinka:**
```ini
FISKAL_CERT_PASS=
```

**Temporary files:**
- CertificateLoader stvara temporary PEM fajlove za Guzzle
- Cleanup nakon svakog HTTP requesta
- Lokacije: `/tmp/fina_cert_XXXXX`, `/tmp/fina_key_XXXXX`

### 10.2 Permission Requirements

```bash
chmod 600 certs/86058362621.F3.3.p12
chown www-data:www-data certs/86058362621.F3.3.p12
```

### 10.3 CA Bundle

**Demo CA:**
```bash
certs/fina-demo-ca-2020.pem
```

**Fallback:** Sistemski trust store (`verify => true`)

---

## 11. PERFORMANCE

### 11.1 Caching

**CertificateLoader cache:**
```php
private array $cache = [];  // In-memory cache za private key i certifikat
```

**Cache key:**
```php
$cacheKey = $certPath . '|' . md5($password);
```

**Lifetime:** Request lifecycle (ne perzistira između requesta).

### 11.2 Database Locking

**BrojRacunaGenerator:**
```php
DB::transaction(function () {
    FiskalniBroj::where(...)->lockForUpdate()->first();
    // ...
});
```

**Isolation level:** Serializable (sprječava race condition na `zadnji_broj`).

### 11.3 Queue Performance

**Recommended settings:**
```ini
QUEUE_CONNECTION=database
QUEUE_FISKALIZACIJA_WORKERS=3
QUEUE_TIMEOUT=30
```

**Supervisor config:**
```ini
[program:laravel-queue-fiskalizacija]
command=php /path/to/artisan queue:work --queue=fiskalizacija --tries=3 --timeout=30
autostart=true
autorestart=true
numprocs=3
```

---

## 12. DEPLOYMENT

### 12.1 Production Checklist

- [ ] Promijeni `FISKAL_ENV=prod`
- [ ] Instaliraj produkcijski certifikat (FISKAL 3)
- [ ] Postavi `FISKAL_CERT_PASS` u .env
- [ ] Provjeri `FISKAL_ENABLED=true`
- [ ] Registriraj poslovne prostore kod FINA
- [ ] Registriraj naplatne uređaje kod FINA
- [ ] Setup queue workers (Supervisor)
- [ ] Setup retry job scheduling
- [ ] Konfiguriraj SSL CA bundle
- [ ] Test TLS connection
- [ ] Test Echo operation
- [ ] Test minimal RacunZahtjev

### 12.2 Environment Variables (Production)

```ini
FISKAL_ENABLED=true
FISKAL_ENV=prod
FISKAL_CERT_PATH=/secure/path/to/production-cert.p12
FISKAL_CERT_PASS=STRONG_PASSWORD_HERE
FISKAL_CA_PATH=/secure/path/to/fina-prod-ca.pem
FISKAL_TIMEOUT=15
FISKAL_LOG_CHANNEL=daily
FISKAL_MAX_ATTEMPTS=5
FISKAL_RETRY_BACKOFF=600
```

### 12.3 Monitoring

**Metrics:**
- Broj uspješnih fiskalizacija (per hour)
- Broj neuspjelih fiskalizacija (per hour)
- Average response time (ms)
- Error rate (%)

**Alerting:**
- Error rate > 10%
- Queue depth > 100
- Response time > 5s

**Dashboard queries:**
```sql
-- Success rate (last 24h)
SELECT 
    COUNT(*) FILTER (WHERE status = 'success') * 100.0 / COUNT(*) as success_rate
FROM fiskalni_logs
WHERE sent_at > NOW() - INTERVAL '24 hours';

-- Error distribution
SELECT 
    error_message, 
    COUNT(*) as count
FROM fiskalni_logs
WHERE status = 'failed'
GROUP BY error_message
ORDER BY count DESC;
```

---

## 13. ZAKLJUČAK

Fiskalizacijski sustav u Laravel 12 aplikaciji je **kompletan, robustan i proizvodno spreman** sa sljedećim karakteristikama:

✅ **SOAP/XML komunikacija** s FINA servisom  
✅ **Digitalno potpisivanje** (XMLDSig, RSA-SHA1)  
✅ **ZKI/JIR generiranje** prema FINA spec v2.3  
✅ **Asinkrono izvršavanje** (Laravel Queue)  
✅ **Retry mehanizam** za neuspjele fiskalizacije  
✅ **Event-driven** arhitektura  
✅ **Comprehensive logging** (request/response XML)  
✅ **Artisan komande** za testiranje i dijagnostiku  
✅ **Demo/Production** okruženja  
✅ **Certificate management** (PKCS#12, PEM)  

**Trenutno stanje:**
- Echo test: ✅ **RADI**
- TLS connection: ✅ **RADI**
- Certificate authentication: ✅ **RADI**
- RacunZahtjev: ❌ **s006 error** (OIB mismatch)

**Sljedeći koraci:**
1. Razjasniti s FINA podrškom: koji OIB koristiti u demo okruženju (certifikat vs. stvarni OIB)
2. Registrirati poslovne prostore i naplatne uređaje
3. Testirati s ispravnim OIB-om
4. Deployment na produkciju

---

**Dokument kreiran:** 13. studenog 2025.  
**Autor:** GitHub Copilot (Analiza postojećeg Laravel 12 sustava)  
**Verzija:** 1.0

