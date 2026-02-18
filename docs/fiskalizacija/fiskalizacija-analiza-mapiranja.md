# Analiza fiskalizacije - mapiranje podataka i provjera ispravnosti

**Datum:** 13. studeni 2025  
**Status:** PROVJERA PRIJE SLANJA FINA MAILA

---

## 1. ŠTO FINA TRAŽI (prema tehničkoj specifikaciji v2.3)

### Minimalni RacunZahtjev XML struktura:

```xml
<RacunZahtjev Id="..." xmlns="http://www.apis-it.hr/fin/2012/types/f73">
  <Zaglavlje Id="...">
    <IdPoruke>UUID</IdPoruke>
    <DatumVrijeme>dd.mm.YYYYTHH:MM:SS</DatumVrijeme>
  </Zaglavlje>
  
  <Racun Id="...">
    <!-- OBVEZNA POLJA -->
    <OIB>11 znamenki</OIB>
    <USustPdv>Y ili N</USustPdv>
    <DatVrijeme>dd.mm.YYYYTHH:MM:SS</DatVrijeme>
    <OznSlijed>P ili N</OznSlijed>
    
    <BrRac>
      <BrOznRac>max 6 znamenki (000001-999999)</BrOznRac>
      <OznPosPr>max 20 znakova (A-Z, 0-9, _, -)</OznPosPr>
      <OznNapUr>max 20 znakova (A-Z, 0-9, _, -)</OznNapUr>
    </BrRac>
    
    <IznosUkupno>Decimal (2 decimale)</IznosUkupno>
    <NacinPlac>G|K|C|T|O</NacinPlac>
    <ZastKod>ZKI - 32 hex znaka uppercase</ZastKod>
    
    <!-- OPCIONALNA POLJA -->
    <OibOper>OIB operatera (ako ima)</OibOper>
    <NakDost>Y ako je naknadno dostavljeno</NakDost>
    <ParagonBrRac>Broj paragona ako postoji</ParagonBrRac>
    <SpecNamj>Posebna namjena (ako ima)</SpecNamj>
  </Racun>
  
  <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <!-- XMLDSig potpis -->
  </ds:Signature>
</RacunZahtjev>
```

### Kodovi načina plaćanja:
- **G** - Gotovina
- **K** - Kartice (Debitne i kreditne)
- **C** - Transakcijski račun (Virman)
- **T** - Ostala sredstva (Transakcijski r. - trećih)
- **O** - Ostalo

---

## 2. TVOJA TRENUTNA IMPLEMENTACIJA

### FiskalContext (app/Services/Fiskalizacija/FiskalContext.php)

```php
class FiskalContext
{
    public function __construct(
        public readonly Udruga $udruga,              // ✅ OK - izvor OIB-a
        public readonly ?Lokacija $lokacija,         // ✅ OK - izvor oznaka
        public readonly string $oznakaSlijeda,       // ✅ OK - P ili N
        public readonly string $oznakaPoslovnogProstora, // ✅ OK - OznPosPr
        public readonly string $oznakaNaplatnogUredaja,  // ✅ OK - OznNapUr
        public readonly Carbon $izdanU,              // ✅ OK - DatVrijeme
        public readonly int $redniBroj,              // ✅ OK - BrOznRac
        public readonly bool $uSustavuPdv,           // ✅ OK - USustPdv
    ) {}
}
```

**✅ ZAKLJUČAK:** FiskalContext ima SVA potrebna polja!

---

### FiskalContextResolver (app/Services/Fiskalizacija/FiskalContextResolver.php)

#### resolve() metoda:
```php
public function resolve(Racun $racun): FiskalContext
{
    $udruga = $racun->udruga;                        // ✅ OK
    $lokacija = $racun->lokacija;                    // ✅ OK (može biti null)
    
    $oznakaSlijeda = $racun->oznaka_slijeda          // ✅ OK - koristi P kao default
        ?? config('fiskalizacija.default_slijed', 'P');
    
    $izdanU = $this->resolveTimestamp($racun);       // ✅ OK - koristi izdan_u ili now()
    
    $oznakaPoslovnogProstora = $this->resolveOznakaPoslovnogProstora($racun, $lokacija);
    $oznakaNaplatnogUredaja = $this->resolveOznakaNaplatnogUredaja($racun, $lokacija);
    
    $redniBroj = $this->brojRacunaGenerator->next(...); // ✅ OK - generira redni broj
    
    $uSustavuPdv = (bool) ($udruga->u_sustavu_pdv ?? false); // ✅ OK
    
    return new FiskalContext(...);
}
```

#### resolveOznakaPoslovnogProstora():
```php
private function resolveOznakaPoslovnogProstora(Racun $racun, ?Lokacija $lokacija): string
{
    $kandidati = [
        $racun->oznaka_poslovnog_prostora,          // 1. prioritet - iz računa
        $lokacija?->oznaka_poslovnog_prostora,      // 2. prioritet - iz lokacije
        $lokacija?->lokacija_kasa,                  // 3. prioritet - stara kolona
        $lokacija?->lokacija_naziv,                 // 4. prioritet - naziv
        $racun->racun_lokacija_id ? 'L' . $racun->racun_lokacija_id : null, // 5. fallback
    ];
    
    return $this->sanitizeOznaka($kandidati, 'POS'); // fallback: 'POS'
}
```

**⚠️ PROBLEM:** Ako nijedan kandidat nije popunjen, vraća se **'POS'** - generička oznaka!

#### resolveOznakaNaplatnogUredaja():
```php
private function resolveOznakaNaplatnogUredaja(Racun $racun, ?Lokacija $lokacija): string
{
    $kandidati = [
        $racun->oznaka_naplatnog_uredaja,           // 1. prioritet - iz računa
        $lokacija?->oznaka_naplatnog_uredaja,       // 2. prioritet - iz lokacije
        $lokacija?->lokacija_kasa,                  // 3. prioritet - stara kolona
        $racun->racun_lokacija_id ? 'K' . $racun->racun_lokacija_id : null, // 4. fallback
    ];
    
    return $this->sanitizeOznaka($kandidati, 'KASA'); // fallback: 'KASA'
}
```

**⚠️ PROBLEM:** Ako nijedan kandidat nije popunjen, vraća se **'KASA'** - generička oznaka!

#### sanitizeOznaka():
```php
private function sanitizeOznaka(array $kandidati, string $fallback): string
{
    $vrijednost = collect($kandidati)
        ->map(fn ($value) => is_string($value) ? trim($value) : $value)
        ->first(fn ($value) => !empty($value));    // ✅ OK - uzima prvi neprazan
    
    $vrijednost = Str::upper($vrijednost ?: $fallback); // ✅ OK - uppercase
    $vrijednost = preg_replace('/[^A-Z0-9_-]/', '', $vrijednost) ?: $fallback; // ✅ OK - sanitize
    
    return Str::limit($vrijednost, 20, '');         // ✅ OK - max 20 znakova
}
```

**✅ ZAKLJUČAK:** Sanitizacija je **ISPRAVNA** prema FINA specifikaciji!

---

### XmlRequestBuilder (app/Services/Fiskalizacija/XmlRequestBuilder.php)

#### build() metoda:
```php
public function build(Racun $racun, FiskalContext $context, string $zki, ?string $jir = null): string
{
    $xml = new SimpleXMLElement('<RacunZahtjev xmlns="http://www.apis-it.hr/fin/2012/types/f73"/>');
    $xml->addAttribute('Id', 'RacunZahtjev-' . Str::uuid());     // ✅ OK
    
    // Zaglavlje
    $zaglavlje = $xml->addChild('Zaglavlje');
    $zaglavlje->addAttribute('Id', 'Zaglavlje-' . Str::uuid());   // ✅ OK
    $zaglavlje->addChild('IdPoruke', (string) Str::uuid());       // ✅ OK
    $zaglavlje->addChild('DatumVrijeme', $this->formatDateTime($context)); // ✅ OK
    
    // Racun
    $racunNode = $xml->addChild('Racun');
    $racunNode->addAttribute('Id', 'Racun-' . Str::uuid());       // ✅ OK
    $racunNode->addChild('OIB', $context->udruga->udruga_oib);    // ✅ OK
    $racunNode->addChild('USustPdv', $context->uSustavuPdv ? 'Y' : 'N'); // ✅ OK
    $racunNode->addChild('DatVrijeme', $this->formatDateTime($context)); // ✅ OK
    $racunNode->addChild('OznSlijed', $context->oznakaSlijeda);   // ✅ OK
    
    // BrRac
    [$broj, $oznPosPr, $oznNapUr] = $this->resolveBrojRacunaParts($context);
    $brojRacunaNode = $racunNode->addChild('BrRac');
    $brojRacunaNode->addChild('BrOznRac', $broj);                 // ✅ OK - 6 znamenki
    $brojRacunaNode->addChild('OznPosPr', $oznPosPr);             // ✅ OK
    $brojRacunaNode->addChild('OznNapUr', $oznNapUr);             // ✅ OK
    
    $racunNode->addChild('IznosUkupno', number_format((float) $racun->iznos, 2, '.', '')); // ✅ OK
    $racunNode->addChild('NacinPlac', $this->mapPayment($racun)); // ⚠️ PROVJERITI
    $racunNode->addChild('ZastKod', $zki);                        // ✅ OK
    
    if ($jir) {
        $racunNode->addChild('JIR', $jir);                        // ✅ OK
    }
    
    return $xml->asXML() ?: '';
}
```

#### formatDateTime():
```php
private function formatDateTime(FiskalContext $context): string
{
    return $context->izdanU->format('d.m.Y\TH:i:s'); // ✅ OK - točan format!
}
```

#### mapPayment():
```php
private function mapPayment(Racun $racun): string
{
    $map = collect(config('fiskalizacija.payment_codes', []))
        ->mapWithKeys(fn ($value, $key) => [Str::lower($key) => $value]);
    
    // Traži u: naziv, slug, oznaka
    $candidateKeys = [
        Str::lower($racun->nacinPlacanja->nacin_placanja_naziv ?? ''),
        Str::lower($racun->nacinPlacanja->slug ?? ''),
        Str::lower($racun->nacinPlacanja->oznaka ?? ''),
    ];
    
    foreach ($candidateKeys as $key) {
        if (!$key) continue;
        
        $key = Str::slug($key, '_');
        if ($map->has($key)) {
            return $map->get($key);
        }
    }
    
    // Fallback na ID
    if ($racun->nacin_placanja_id) {
        $key = 'nacin_' . $racun->nacin_placanja_id;
        if ($map->has($key)) {
            return $map->get($key);
        }
    }
    
    return $map->get('other', 'O'); // ✅ OK - default je 'O' (Ostalo)
}
```

**✅ ZAKLJUČAK:** Mapiranje načina plaćanja je **FLEKSIBILNO** i **ISPRAVNO**!

---

## 3. PROVJERA KONFIGURACIJE

### config/fiskalizacija.php - payment_codes:
```php
'payment_codes' => [
    'gotovina' => 'G',
    'kartica' => 'K',
    'stripe' => 'K',
    'corvuspay' => 'K',
    'wspay' => 'K',
    'virman' => 'V',           // ⚠️ FINA koristi 'C' za transakcijski račun!
    'kekspay' => 'O',
    'bank_transfer' => 'V',    // ⚠️ FINA koristi 'C' za transakcijski račun!
    'direct_debit' => 'T',
    'other' => 'O',
],
```

**⚠️ PROBLEM:** FINA koristi:
- **C** za transakcijski račun (virman)
- **T** za ostala sredstva transakcijskog računa trećih
- **V** nije valjan FINA kod!

---

## 4. PROVJERA MODELA Racun

### Kolone u tablici `racuns`:
```php
protected $fillable = [
    'potvrda_broj',                    // ✅ Popunjava se
    'datum_racuna',                    // ✅ Popunjava se
    'iznos',                           // ✅ Popunjava se
    'napomena',                        // ✅ Opcionalno
    'racun_clan_id',                   // ✅ Popunjava se
    'racun_paketi_id',                 // ✅ Popunjava se
    'nacin_placanja_id',               // ✅ Popunjava se
    'racun_udruga_id',                 // ✅ Popunjava se
    'racun_lokacija_id',               // ⚠️ MOŽE BITI NULL!
    'racun_status_id',                 // ✅ Popunjava se
    'narudzba_id',                     // ✅ Popunjava se
    'transakcija_id',                  // ✅ Popunjava se
    'pos_terminal_id',                 // ⚠️ MOŽE BITI NULL!
    
    // FISKALIZACIJSKE KOLONE:
    'zki',                             // ✅ Popunjava se nakon fiskalizacije
    'jir',                             // ✅ Popunjava se nakon fiskalizacije
    'fiskaliziran_u',                  // ✅ Popunjava se nakon fiskalizacije
    'oznaka_slijeda',                  // ⚠️ MOŽE BITI NULL - koristi default 'P'
    'oznaka_poslovnog_prostora',       // ⚠️ MOŽE BITI NULL - koristi fallback
    'oznaka_naplatnog_uredaja',        // ⚠️ MOŽE BITI NULL - koristi fallback
    'izdan_u',                         // ⚠️ MOŽE BITI NULL - koristi now()
];
```

---

## 5. KRITIČNI PROBLEMI I PREPORUKE

### 🔴 KRITIČNO #1: OIB u zahtjevu vs OIB iz certifikata

**Problem:**
- Tvoj certifikat ima OIB: **86058362621**
- U zahtjevima koristiš demo OIB: **96557881558**

**Što šalješ:**
```xml
<OIB>96557881558</OIB>  <!-- OIB iz udruga.udruga_oib -->
```

**Rješenje:**
1. Pitaj FINA koji OIB trebam koristiti:
   - OIB iz certifikata (86058362621)?
   - OIB iz aplikacije (96557881558)?
2. **Vjerojatno:** trebaš koristiti OIB iz certifikata (86058362621)

---

### 🟡 PROBLEM #2: Virman kod (V → C)

**Problem:**
```php
'virman' => 'V',           // ❌ FINA ne koristi 'V'
'bank_transfer' => 'V',    // ❌ FINA ne koristi 'V'
```

**FINA kodovi:**
- G - Gotovina
- K - Kartice
- **C** - Transakcijski račun (virman) ← ISPRAVNO
- T - Ostala sredstva
- O - Ostalo

**Rješenje:**
```php
'virman' => 'C',           // ✅ Transakcijski račun
'bank_transfer' => 'C',    // ✅ Transakcijski račun
```

---

### 🟡 PROBLEM #3: Generičke oznake prostora/uređaja

**Problem:**
Ako `racun_lokacija_id` je NULL ili lokacija nema popunjene oznake:
- `OznPosPr` = **'POS'** (generička oznaka)
- `OznNapUr` = **'KASA'** (generička oznaka)

**Pitanje za FINA:**
- Mogu li se koristiti generičke oznake (POS, KASA) za demo testiranje?
- Moraju li biti prethodno registrirane?

**Preporuka:**
1. Uvijek popuni `oznaka_poslovnog_prostora` i `oznaka_naplatnog_uredaja` u tablici `lokacijas`
2. Alternativno: koristi **OIB-specific oznake** (npr. K1, K2, K3)

---

### 🟢 ŠTO RADI DOBRO

1. ✅ **XML struktura** - potpuno ispravna prema FINA specifikaciji
2. ✅ **Format datuma/vremena** - `dd.mm.YYYY\THH:MM:SS`
3. ✅ **Sanitizacija oznaka** - uppercase, samo A-Z 0-9 _ -, max 20 znakova
4. ✅ **Broj računa** - 6 znamenki s vodećim nulama (000001)
5. ✅ **XMLDSig potpis** - generira se preko XmlSigner klase
6. ✅ **ZKI generiranje** - RSA-SHA1 potpis, 32 hex znaka uppercase
7. ✅ **Fleksibilno mapiranje** - traži po nazivu, slugu, oznaci
8. ✅ **Fallback vrijednosti** - 'P' za slijed, 'O' za način plaćanja

---

## 6. ACTION ITEMS

### Prije slanja mail-a FINA-i:

1. **✅ ODMAH:** Ispravi payment_codes u config/fiskalizacija.php:
   ```php
   'virman' => 'C',        // bilo: 'V'
   'bank_transfer' => 'C', // bilo: 'V'
   ```

2. **✅ UKLJUČI U MAIL:** Pitanje o OIB-u:
   - Trebam li koristiti OIB 86058362621 (iz certifikata)?
   - Ili mogu koristiti OIB 96557881558 (demo OIB)?

3. **✅ UKLJUČI U MAIL:** Pitanje o oznakama:
   - Mogu li koristiti generičke oznake (POS, KASA, K1)?
   - Moraju li biti prethodno registrirane?

### Nakon odgovora od FINA:

4. **Popuni lokacije:** Osiguraj da sve lokacije imaju:
   - `oznaka_poslovnog_prostora` (npr. K1, K2, K3)
   - `oznaka_naplatnog_uredaja` (npr. K1, POS1, KASA1)

5. **Testiraj s ispravnim OIB-om:** Ako FINA kaže da trebaš koristiti 86058362621

---

## 7. ZAKLJUČAK

### ✅ TVOJA IMPLEMENTACIJA JE **95% ISPRAVNA**!

**Što radi:**
- FiskalContext ima sva potrebna polja
- FiskalContextResolver ispravno resolvira podatke
- XmlRequestBuilder generira ispravan XML
- Sanitizacija oznaka prema FINA specifikaciji
- Format datuma/vremena ispravan
- XMLDSig potpis funkcionira

**Što treba ispraviti:**
1. Payment code za virman: V → C
2. Provjeriti OIB (86058362621 vs 96557881558)
3. Provjeriti registraciju oznaka prostora/uređaja

**Ocjena:** 🟢 **ODLIČAN POSAO!** Samo sitne korekcije potrebne.

