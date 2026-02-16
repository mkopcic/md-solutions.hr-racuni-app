# Korisnički Vodič - Računi Aplikacija

**Datum ažuriranja:** 16. veljače 2026  
**Verzija:** 2.0

---

## Sadržaj

1. [Prijava u Sustav](#prijava-u-sustav)
2. [Kreiranje Novog Računa](#kreiranje-novog-računa)
3. [Tipovi Računa](#tipovi-računa)
4. [Način Plaćanja](#način-plaćanja)
5. [PDV Kalkulacija](#pdv-kalkulacija)
6. [Generiranje PDF-a](#generiranje-pdf-a)
7. [QR Kod za Plaćanje](#qr-kod-za-plaćanje)
8. [Često Postavljana Pitanja](#često-postavljana-pitanja)

---

## Prijava u Sustav

1. Otvori web preglednik (Chrome, Firefox, Edge)
2. Idi na: `https://obrt-racuni-laravel-app.test/`
3. Ulogiraj se sa svojim korisničkim podacima
4. Nakon prijave, prikazat će se dashboard

---

## Kreiranje Novog Računa

### Korak 1: Otvaranje Forme

1. Klikni na **"Računi"** u glavnom meniju
2. Klikni na **"Novi račun"** gumb

### Korak 2: Osnovni Podaci

#### Tip Računa
Odaberi tip računa iz padajućeg izbornika:
- **SPO** - Standardni račun (najčešće korišteno)
- **AMK** - Advanced Model
- **FCZ** - Foreign Currency Zone
- **SFL** - Special Fiscal Label
- **WDR** - Withdrawal

💡 **Savjet:** Za većinu slučajeva koristi **SPO**.

#### Broj Računa
- **Automatski se generira**
- Ne možeš mijenjati broj ručno
- Format: `broj/mjesec/1/tip` (npr. **1/2/1/SPO**)
- Svaki tip ima vlastitu sekvencu
- Preview se prikazuje ispod polja

#### Način Plaćanja
- **Virman** - Bankovni prijenos (default)
- **Gotovina** - Cash plaćanje
- **Kartica** - Kartično plaćanje

### Korak 3: Kupac i Datumi

#### Kupac
- Odaberi kupca iz padajućeg izbornika
- Prikazuje se: Ime kupca (OIB)
- Ako kupac ne postoji, prvo ga dodaj u "Kupci" sekciju

#### Datumi
- **Datum izdavanja** - Kada je račun izdan (default: danas)
- **Datum isporuke** - Kada je usluga/roba isporučena (default: danas)
- **Datum dospijeća** - Rok plaćanja (default: +15 dana)

### Korak 4: Dodavanje Stavki

#### Dodavanje Nove Stavke
Klikni **"Dodaj stavku"** gumb.

#### Popunjavanje Stavke

1. **Opis**
   - Unesi naziv usluge/proizvoda
   - Ili odaberi iz postojećih usluga (dropdown ispod)

2. **Jedinica Mjere (Jed.mj.)**
   - **kom** - komada (default)
   - **sat** - sati
   - **dan** - dani

3. **Količina**
   - Unesi broj jedinica
   - Decimalne vrijednosti dopuštene (npr. 1.5)

4. **Cijena**
   - Cijena po jedinici **bez PDV-a**
   - Unosi se u EUR
   - Decimale: 0.00

5. **Popust (%)**
   - Postotak popusta (opcionalno)
   - Primjer: 10 = 10% popusta

6. **PDV %**
   - Odaberi poreznu stopu iz padajućeg izbornika
   - Standardno: **25%** (najviša stopa u Hrvatskoj)
   - Druge dostupne: 13%, 5%, 0%

7. **PDV Iznos**
   - **Automatski se računa**
   - Ne možeš mijenjati ručno

8. **Ukupno**
   - **Automatski se računa**
   - Uključuje osnovicu + PDV

#### Brisanje Stavke
Klikni ikonu 🗑️ (smeće) desno od stavke.

### Korak 5: Totali

Ispod tablice vidiš:
- **OSNOVICA (bez PDV-a):** Zbroj svih stavki bez PDV-a
- **PDV UKUPNO:** Zbroj svih PDV iznosa
- **UKUPNO ZA NAPLATU:** Konačan iznos za naplatu

### Korak 6: Napomene (Opcionalno)

#### Napomena
- Prikazuje se na PDF računu
- Primjer: "Obveznik je u sustavu PDV-a"

#### Napomena za avansno plaćanje
- Ako je dio ili cjeli račun plaćen unaprijed
- Primjer: "Plaćeno avansom 500 EUR"

### Korak 7: Spremanje

Klikni **"Spremi račun"** gumb.

---

## Tipovi Računa

### SPO (Standard Payment Order)
- Najčešće korišteni tip
- Za standardno poslovanje
- Vlastita sekvenca brojeva

### AMK (Advanced Model Kategorija)
- Za naprednije modele
- Vlastita sekvenca brojeva

### FCZ (Foreign Currency Zone)
- Za strane valute (ako primjenjivo)
- Vlastita sekvenca brojeva

### SFL (Special Fiscal Label)
- Specijalne fiskalne oznake
- Vlastita sekvenca brojeva

### WDR (Withdrawal)
- Za povlačenja sredstava
- Vlastita sekvenca brojeva

### Brojanje Računa

**Primjeri:**
- Prvi SPO račun u siječnju: `1/1/1/SPO`
- Drugi SPO račun u siječnju: `2/1/1/SPO`
- Prvi SPO račun u veljači: `1/2/1/SPO` (ne resetira se, nastavlja sekvencu)
- Prvi AMK račun u siječnju: `1/1/1/AMK` (nezavisna sekvenca)

🔢 **Pravilo:** Svaki tip ima vlastitu sekvencu koja se resetira svake godine.

---

## Način Plaćanja

### Virman (Bankovni prijenos)
- Default opcija
- Za plaćanja putem banke
- IBAN i poziv na broj vidljivi na računu
- Uključen QR kod za lakše plaćanje

### Gotovina
- Za cash plaćanja
- Bez QR koda

### Kartica
- Za kartična plaćanja
- POS terminal ili online

---

## PDV Kalkulacija

### Kako Radi?

1. **Osnovna cijena:** Količina × Cijena
2. **Nakon popusta:** Osnovna cijena - (Osnovna cijena × Popust%)
3. **PDV iznos:** Cijena nakon popusta × (PDV% / 100)
4. **Ukupno:** Cijena nakon popusta + PDV iznos

### Primjer

```
Usluga: Web razvoj
Količina: 10 sati
Cijena: 50 EUR/sat
Popust: 10%
PDV: 25%

Kalkulacija:
Osnovna cijena: 10 × 50 = 500 EUR
Nakon popusta: 500 - (500 × 10%) = 450 EUR
PDV iznos: 450 × 25% = 112.50 EUR
Ukupno za naplatu: 450 + 112.50 = 562.50 EUR
```

### PDV Stope u Hrvatskoj

| Stopa | Primjena |
|-------|----------|
| 25% | Opća stopa (services, most goods) |
| 13% | Snižena stopa (some food, hotels) |
| 5% | Posebno snižena stopa (books, newspapers) |
| 0% | Oslobođeno PDV-a (exports, special cases) |

---

## Generiranje PDF-a

### Prikaz PDF-a (u pregledniku)

1. Otvori račun (klikni na račun u listi)
2. Klikni **"Prikaži PDF"** ili **"Pregled"** gumb
3. PDF se otvara u novom tabu

### Preuzimanje PDF-a

1. Otvori račun
2. Klikni **"Preuzmi PDF"** gumb
3. PDF se preuzima u Downloads folder

### Što sadrži PDF?

✅ **Header:**
- Logo/naziv obrta
- Plava linija
- Tagline: "obrt za računalno programiranje"

✅ **Business Info:**
- Naziv, adresa, OIB, IBAN

✅ **Broj Računa:**
- Format: "RAČUN br: 1-1-1-SPO"

✅ **Kupac:**
- Ime, adresa, grad, OIB

✅ **Datumi:**
- Izdavanja, isporuke, dospijeća

✅ **Tablica Stavki:**
- R.br, Opis, Jed.mj., Količina, Cijena, Iznos, Popust, PDV%

✅ **PDV Razrada:**
- Osnovica (bez PDV-a)
- PDV stopa i iznos
- Ukupno za naplatu

✅ **Napomene:**
- Opcionalne napomene

✅ **Način Plaćanja:**
- Virman/Gotovina/Kartica
- Rok plaćanja

✅ **QR Kod:**
- Za digitalno plaćanje (HUB3 standard)

✅ **Footer:**
- Kontakt informacije
- Pravna napomena

---

## QR Kod za Plaćanje

### Što je HUB3 QR Kod?

HUB3 je **hrvatski standard za plaćanja** pomoću QR kodova. Omogućava jednostavno plaćanje skeniranjem QR koda mobilnom banking aplikacijom.

### Kako Koristiti?

#### Za Primatelja (Vi)
1. Generiraj PDF račun
2. QR kod automatski se prikazuje na dnu računa
3. Pošalji PDF kupcu (email, print)

#### Za Platitelja (Kupac)
1. Otvori mobilnu banking aplikaciju
2. Odaberi "Novo plaćanje" ili "Scan QR"
3. Skeniraj QR kod sa računa
4. Svi podaci automatski popunjeni:
   - IBAN primatelja
   - Iznos plaćanja
   - Referentni broj (broj računa)
   - Opis plaćanja
5. Potvrdi plaćanje

### Što Sadrži QR Kod?

- 🏦 **IBAN** - Broj računa primatelja
- 💶 **Iznos** - Točan iznos za platiti
- 🔢 **Poziv na broj** - Broj računa (npr. 1-1-1-SPO)
- 📝 **Opis** - "Racun {broj}"
- 🏷️ **Šifra namjene** - GDSV (goods/services)
- 🇭🇷 **Model plaćanja** - HR00

### Podržavaju li sve banke?

Da, sve hrvatske banke podržavaju HUB3 standard. Najčešće aplikacije:
- m-zaba (Zagrebačka banka)
- George (Erste banka)
- PBZ mobile banking
- Addiko Mobile
- OTP banka
- Itd.

---

## Često Postavljana Pitanja

### Kako promijeniti tip računa nakon što sam kreirao račun?
Trenutno se tip ne može mijenjati nakon kreiranja. Moraš kreirati novi račun sa ispravnim tipom.

### Može li se broj računa mijenjati ručno?
Ne, broj računa se automatski generira i ne može se mijenjati ručno. To osigurava jedinstveni i konzistentni sustav brojanja.

### Što ako griješim broj računa?
Broj računa je automatski generiran i ne može biti pogrešan. Ako postoji drugi problem, kontaktiraj administratora.

### Kako dodati novu poreznu stopu?
Idi na **"Postavke"** → **"Porezne stope"** i dodaj novu stopu.

### Mogu li imati više valuta?
Trenutno je podržan samo EUR. Podrška za više valuta je planirana za buduće verzije.

### Kako dodati novi tip računa?
Tipovi računa su hardkodirani (SPO, AMK, FCZ, SFL, WDR). Za dodavanje novog tipa kontaktiraj developera.

### Što ako QR kod ne radi?
Provjeri da:
1. IBAN u Business postavkama je ispravan
2. Iznos računa je pozitivan
3. Banking app podržava HUB3 (sve hrvatske banke podržavaju)

### Mogu li kreirati račun bez PDV-a?
Da, postavi PDV stopu na 0% za stavke bez PDV-a.

### Kako obrisati račun?
Trenutno brisanje nije moguće iz sigurnosnih razloga. Račun može biti označen kao storniran (ako je implementirano).

### Kako poslati račun emailom kupcu?
Trenutno nema automatskog slanja. Preuzmi PDF i pošalji ručno putem email klijenta.

### Gdje mogu vidjeti sve račune određenog kupca?
U listi računa možeš filtrirati po kupcu (ako je filter implementiran) ili koristi pretraživanje.

---

## Kontakt za Podršku

Za tehničku podršku ili pitanja:
- Provjeri dokumentaciju u `docs` folderu
- Za bug report, kontaktiraj administratora
- Za nove features, podnesi zahtjev

---

## Dodatni Resursi

- **[Tehnička Dokumentacija](CHANGELOG_PDF_REDESIGN.md)** - Za developere
- **[Instalacijska Dokumentacija](INSTALLATION.md)** - Setup i konfiguracija
- **[Laravel Dokumentacija](https://laravel.com/docs)** - Backend framework
- **[Livewire Dokumentacija](https://livewire.laravel.com)** - Frontend reactive komponente

---

**Verzija:** 2.0  
**Zadnje ažurirano:** 16.02.2026  
**Status:** ✅ Aktivno
