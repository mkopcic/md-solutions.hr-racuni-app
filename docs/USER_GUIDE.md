# Korisnički Vodič - Računi Aplikacija

**Datum ažuriranja:** 18. veljače 2026  
**Verzija:** 2.2

---

## Sadržaj

1. [Prijava u Sustav](#prijava-u-sustav)
2. [Kreiranje Novog Računa](#kreiranje-novog-računa)
3. [Tipovi Računa](#tipovi-računa)
4. [Način Plaćanja](#način-plaćanja)
5. [PDV Kalkulacija](#pdv-kalkulacija)
6. [Generiranje PDF-a](#generiranje-pdf-a)
7. [PDF417 Barkod za Plaćanje](#pdf417-barkod-za-plaćanje)
8. [e-Račun Integracija](#e-račun-integracija)
9. [Postavke (Settings)](#postavke-settings)
   - [Business Postavke](#business-postavke)
   - [Favicon Postavke](#favicon-postavke)
   - [Email Postavke](#email-postavke)
10. [Activity Logs](#activity-logs)
11. [Često Postavljana Pitanja](#često-postavljana-pitanja)

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

## PDF417 Barkod za Plaćanje

### Što je HUB3 PDF417 Barkod?

HUB3 je **hrvatski standard za plaćanja** pomoću 2D barkodova. Koristi **PDF417 barkod** (pravokutni crno-bijeli barkod) za jednostavno plaćanje skeniranjem mobilnom banking aplikacijom.

**Važno:** HUB3 standard koristi **PDF417 barkod**, ne QR kod! PDF417 je specifičan 2D barkod optimiziran za finance i bankarstvo.

### Kako Koristiti?

#### Za Primatelja (Vi)
1. Generiraj PDF račun
2. PDF417 barkod automatski se prikazuje na dnu računa (pravokutni, crno-bijeli)
3. Pošalji PDF kupcu (email, print)

#### Za Platitelja (Kupac)
1. Otvori mobilnu banking aplikaciju
2. Odaberi "Novo plaćanje" ili "Scan barcode"
3. Skeniraj PDF417 barkod sa računa
4. Svi podaci automatski popunjeni:
   - IBAN primatelja
   - Iznos plaćanja
   - Referentni broj (broj računa)
   - Opis plaćanja
5. Potvrdi plaćanje

### Što Sadrži PDF417 Barkod?

- 🏦 **IBAN** - Broj računa primatelja
- 💶 **Iznos** - Točan iznos za platiti (u centima)
- 🔢 **Poziv na broj** - Broj računa (npr. 1-1-1-SPO)
- 📝 **Opis** - "Racun br. {broj}"
- 🏷️ **Šifra namjene** - COST (goods/services)
- 🇭🇷 **Model plaćanja** - HR01

### Podržavaju li sve banke?

Da, sve hrvatske banke podržavaju HUB3 standard s PDF417 barkodom. Najčešće aplikacije:
- PBZ bank (PBZ mobile banking)
- Zagrebačka banka (m-zaba)
- Erste banka (George)
- OTP banka
- Addiko Mobile
- Revolut
- I sve druge hrvatske banke

---

## e-Račun Integracija

### Što je e-Račun?

**e-Račun** je sustav elektroničke razmjene računa između poslovnih subjekata u Republici Hrvatskoj. Omogućava automatiziranu obradu računa kroz FINA sustav prema UBL 2.1 standardu.

### Glavne Funkcionalnosti

#### 1. Primanje Dolaznih Računa
- Automatsko preuzimanje računa iz FINA sustava
- Parsiranje UBL 2.1 XML formata
- Spremanje u `incoming_invoices` i `incoming_invoice_items` tablice
- Praćenje statusa (pending, approved, rejected)

#### 2. Slanje Izlaznih Računa
- Konverzija računa u UBL 2.1 XML format
- XML potpis (XMLDSig sa certifikatom)
- Slanje na FINA AS4 gateway
- Praćenje statusa dostave

#### 3. Status Sinkronizacija
- Automatska provjera statusa računa na FINA sustavu
- Ažuriranje lokalnih statusa (sent, delivered, viewed, error)
- Logiranje svake komunikacije u `eracun_logs`

### Konfiguracija e-Računa

Konfiguracijska datoteka: `config/eracun.php`

**Potrebne Environment Varijable (.env):**

```env
# FINA API Credentials
FINA_API_URL=https://era-test.fina.hr/api/v1
FINA_USERNAME=your_username
FINA_PASSWORD=your_password
FINA_CERTIFICATE_PATH=storage/certs/your-cert.p12
FINA_CERTIFICATE_PASSWORD=cert_password

# Business Credentials
FINA_VAT_ID=12345678901
FINA_GLN=3850000000000
```

### Artisan Komande

#### Testiranje Konekcije
```bash
php artisan eracun:test
```
Testira FINA API konekciju, autentifikaciju, i certifikat.

#### Sinkronizacija Statusa
```bash
php artisan invoices:sync-status
```
Ažurira statuse svih računa iz FINA sustava.

#### Uvoz iz Excel-a
```bash
php artisan invoices:import path/to/file.xlsx
```
Masovni uvoz računa iz Excel datoteke.

#### Slanje Izvještaja
```bash
php artisan invoices:send-report --email=admin@example.com
```
Šalje izvještaj o računima emailom.

### e-Račun Modeli

#### IncomingInvoice
Dolazni računi primljeni preko e-Račun sustava.

**Glavna polja:**
- `supplier_name`, `supplier_vat_id` - Dobavljač
- `invoice_number`, `issue_date` - Podaci o računu
- `total_amount`, `tax_amount` - Financijski podaci
- `status` - pending/approved/rejected
- `ubl_xml` - Originalni UBL XML

#### EracunLog
Logiranje svih e-Račun operacija.

**Logira:**
- API pozive (send, receive, status_check)
- Uspješne i neuspješne operacije
- Request/Response tijela
- Error detalje

### Dokumentacija

Za više tehničkih detalja:
- **[FINA_E_RACUN_INTEGRACIJA.md](FINA_E_RACUN_INTEGRACIJA.md)** - Puni tehnički guide
- **[e-racun/ERACUN_SETUP.md](e-racun/ERACUN_SETUP.md)** - Setup instrukcije
- **[e-racun/DATABASE_ARCHITECTURE.md](e-racun/DATABASE_ARCHITECTURE.md)** - Arhitektura baze

---

##  Postavke (Settings)

### Pristup Postavkama

1. Klikni na svoj profil (dolje lijevo u sidebaru)
2. Odaberi **"Settings"**
3. U lijevom sidebar-u prikazuju se sve dostupne postavke:
   - Profile
   - Password
   - Appearance
   - **Business** ⭐ Novo
   - **Favicon** ⭐ Novo
   - **Email** ⭐ Novo

---

### Business Postavke

**Osnovni podaci o obrtu/tvrtki**

#### Pristup Business Postavkama

1. Idi na **Settings → Business**
2. Prikazuje se forma sa svim business podacima

#### Osnovna Polja

**Naziv tvrtke** - Službeni naziv obrta/tvrtke
- Primjer: "Obrt za računalno programiranje Marko Kopčić"

**Adresa** - Puna poslovna adresa
- Primjer: "Ulica Josipa Jurja Strossmayera 123"

**Grad** - Mjesto poslovanja
- Primjer: "Zagreb"

**OIB** - Osobni identifikacijski broj (11 znamenki)
- Primjer: "12345678901"

**IBAN** - Broj računa
- Format: HR + 19 znamenki
- Primjer: "HR1234567890123456789"

**Email** - Kontakt email adresa

**Telefon** - Kontakt telefon

#### e-Račun & Fiskalizacija Polja ⭐

**U PDV sustavu** (`in_vat_system`)
- Checkbox (Da/Ne)
- Označava ako je obrt obveznik PDV-a
- 🔴 **Obavezno za e-Račun integraciju**
- Ako je označeno, mora se iskazivati PDV na računima

**Oznaka poslovnog prostora** (`business_space_label`)
- Tekstualno polje
- Primjer: "POS1", "OFFICE", "POSLOVNICA-ZG"
- 🔴 **Obavezno za e-Račun i fiskalizaciju**
- Jedinstvena oznaka poslovnog prostora za FINA

**Oznaka blagajne** (`cash_register_label`)
- Tekstualno polje
- Primjer: "KASA1", "BLAGAJNA-01", "POS-TERMINAL-1"
- 🔴 **Obavezno za e-Račun i fiskalizaciju**
- Jedinstvena oznaka naplatnog uređaja

#### Logo Upload

**Logo datoteka** - PNG, JPG, ili SVG (maks 2MB)
- Upload loga tvrtke
- Prikazuje se na PDF računima
- Preporučena dimenzija: 300x100px

#### Spremanje

1. Popuni sve potrebne podatke
2. Klikni **"Save Business"**
3. Prikazuje se **"Business updated successfully"** poruka

💡 **Savjet:** Ako koristis e-Račun sustav, **obavezno** popuni sva tri nova polja (U PDV sustavu, Oznaka poslovnog prostora, Oznaka blagajne).

---

### Favicon Postavke

**Što su favicon slike?**
Favicon slike su male ikone koje se prikazuju u browser tab-u, bookmarks-ima, i mobilnim shortcut-ima.

#### Vrste Favicon Datoteka

1. **favicon.ico** - Klasična favicon slika (16x16px ili 32x32px)
2. **favicon.svg** - Moderna SVG verzija (scalable)
3. **apple-touch-icon.png** - Ikona za iOS (180x180px)

#### Upload Favicon Slika

1. Idi na **Settings → Favicon**
2. Prikazan je status trenutnih favicon datoteka:
   - 🟢 **Zeleni badge** = Datoteka postoji
   - ⚪ **Sivi badge** = Datoteka ne postoji

3. Za upload novih favicon slika:
   - Klikni **"Choose file"** pored željene vrste
   - Odaberi datoteku (.ico, .svg, ili .png)
   - Datoteke moraju biti **maks 1MB**

4. Klikni **"Upload Favicons"**
5. Datoteke se automatski kopiraju u `public` direktorij

#### Preporuke

- **favicon.ico** - Export iz Photoshop/Figma kao .ico ili koristi online converter
- **favicon.svg** - Najkvalitetnija opcija za moderne preglednike
- **apple-touch-icon.png** - 180x180px PNG za iOS home screen ikone

💡 **Tip:** Možeš uploadati sve tri datoteke odjednom ili pojedinačno.

---

### Email Postavke

**SMTP konfiguracija za slanje emailova iz aplikacije**

#### Pristup Email Postavkama

1. Idi na **Settings → Email**
2. Prikazuje se forma s dvije sekcije:
   - Mail Configuration (osnovne postavke)
   - SMTP Server Settings (serverirane postavke)

#### Mail Configuration

**Mail Driver** - Odaberi metodu slanja
- `SMTP` - Standardni SMTP server (preporučeno)
- `Sendmail` - Linux sendmail
- `Mailgun` - Mailgun API
- `Amazon SES` - Amazon Simple Email Service
- `Postmark` - Postmark API
- `Log` - Samo za development (ne šalje emailove)

**From Email** - Email adresa s koje se šalju emailovi
- Primjer: `noreply@example.com`
- Mora biti validna email adresa

**From Name** - Ime pošiljatelja
- Primjer: "Računi Obrt"
- Prikazuje se kao ime pošiljatelja u email klijentu

#### SMTP Server Settings

⚠️ **Ova sekcija se prikazuje samo ako je odabran SMTP driver.**

**Host** - SMTP server adresa
- Primjer: `smtp.gmail.com`, `smtp.mailtrap.io`, `mailpit` (local)

**Port** - SMTP port
- `587` - TLS (najčešće korišteno)
- `465` - SSL
- `2525` - Alternativni port

**Username** - SMTP korisničko ime
- Najčešće je to email adresa

**Password** - SMTP lozinka
- Za Gmail koristi **App Password**, ne običnu lozinku

**Encryption** - Tip enkripcije
- `TLS` - TLS encryption (Port 587)
- `SSL` - SSL encryption (Port 465)
- `None` - Bez enkripcije (nesigurno)

#### Spremanje Postavki

1. Popuni sve potrebne podatke
2. Klikni **"Save Settings"**
3. Postavke se spremaju u `.env` file
4. Config cache se automatski cleara
5. Prikazuje se **"Saved."** poruka

#### Test Email Funkcionalnost

Nakon spremanja postavki, možeš poslati testni email:

1. U desnom stupcu nalazi se **"Test Email"** sekcija
2. Popuni:
   - **Recipient Email** - Email na koji želiš poslati test
   - **Email Subject** - Naslov emaila
   - **Email Message** - Sadržaj testnog emaila

3. Klikni **"Send Test Email"**
4. Prikazuje se jedna od poruka:
   - 🟢 **Zelena poruka** = Email uspješno poslan
   - 🔴 **Crvena poruka** = Greška pri slanju (prikazuje detalje greške)

💡 **Tip:** Ako koristiš **Mailpit** (local development), testiraj na http://localhost:8025/

#### Active Configuration

Lijevo od Test Email sekcije prikazuje se trenutna aktivna konfiguracija:
- Mail Driver
- SMTP Host
- SMTP Port
- From Address
- From Name

**Važno:** Ovo prikazuje konfiguraciju koja je trenutno učitana u aplikaciji, ne obavezno onu koju si upravo unio. Nakon spremanja, ova sekcija se ažurira.

#### Najčešći Problemi

**"Failed to send" greška**
- Provjeri SMTP Host, Port, Username, Password
- Gmail zahtijeva App Password, ne običnu lozinku
- Provjeri firewall postavke

**"SMTP settings are not configured"**
- Prvo spremi postavke klikom na "Save Settings"
- Tek tada možeš slati test emailove

**Test email se vrti u krug bez poruke**
- Bug je riješen verzijom 2.1
- Koristi Livewire reactive properties za feedback

#### Gmail SMTP Postavke

Za Gmail koristi:
- **Host:** `smtp.gmail.com`
- **Port:** `587`
- **Encryption:** `TLS`
- **Username:** твоја Gmail adresa
- **Password:** **App Password** (ne običnu lozinku!)
  - Generiraj App Password: https://myaccount.google.com/apppasswords

#### Mailtrap SMTP Postavke (Development)

Za testiranje u development environmentu koristi Mailtrap:
- **Host:** `smtp.mailtrap.io`
- **Port:** `2525`
- **Username:** (iz Mailtrap Inbox)
- **Password:** (iz Mailtrap Inbox)
- **Encryption:** `TLS`

#### Mailpit SMTP Postavke (Local Development)

Ako koristiš Laravel Sail ili Mailpit lokalno:
- **Host:** `mailpit`
- **Port:** `1025`
- **Username:** (ostaviti prazno)
- **Password:** (ostaviti prazno)
- **Encryption:** `None`
- Web interface: http://localhost:8025/

---

## Activity Logs

### Što su Activity Logs?

**Activity Logs** prikazuju sve aktivnosti i promjene koje se dešavaju u aplikaciji. Sustav automatski bilježi:
- Kreiranje, uređivanje, brisanje računa
- Kreiranje, uređivanje kupaca
- Kreiranje, uređivanje usluga
- Izmjene postavki
- Prijave korisnika
- Sve ostale važne operacije

### Pristup Activity Logs

1. Klikni na **"Activity Logs"** u glavnom menu
2. Prikazuje se lista svih aktivnosti

### Što Prikazuje?

Za svaku aktivnost vidiš:
- **Event** - Tip aktivnosti (created, updated, deleted)
- **Description** - Opis što je napravljeno
- **Subject Type** - Model koji je izmjenjen (Invoice, Customer, Service)
- **Causer** - Korisnik koji je napravio promjenu
- **Time** - Vrijeme kada se dogodilo

### Filtriranje Logova

Možeš filtrirati logove po:
- **Event** - Tip aktivnosti (created/updated/deleted)
- **Subject Type** - Tip modela
- **Pretrazivanje** - Pretraživanje po opisu

### Brisanje Svih Logova ⭐

**Nova funkcionalnost:** Možeš obrisati sve logove odjednom.

#### Kako Obrisati Sve Logove?

1. Idi na **Activity Logs** stranicu
2. U gornjem desnom kutu vidiš crveni gumb **"Obriši sve logove"**
3. Klikni na gumb
4. Prikazuje se confirmation dialog: **"Jeste li sigurni? Ova akcija je nepovratna."**
5. Potvrdi brisanje

#### Što Se Briše?

Ova akcija briše **TRI vrste logova**:

1. **Spatie Activity Logs** - Svi logovi iz `activity_log` tablice
2. **Laravel Application Logs** - Sve `.log` datoteke iz `storage/logs/` direktorija
3. **Browser/Debugbar Logs** - Svi cache fajlovi iz `storage/debugbar/` direktorija

⚠️ **Upozorenje:** Ova akcija je **nepovratna**! Nakon brisanja, logovi se ne mogu vratiti.

💡 **Savjet:** Koristi ovu funkciju periodicno za čišćenje starih logova i oslobođanje diskovnog prostora.

#### Uspjesno Brisanje

Nakon uspješnog brisanja prikazuje se zelena poruka:
- "✅ Svi logovi su uspješno obrisani."

#### Greška Pri Brisanju

Ako dođe do greške, prikazuje se crvena poruka s detaljima:
- "❌ Greška pri brisanju logova: [detalji greške]"

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

**Verzija:** 2.2  
**Zadnje ažurirano:** 18.02.2026  
**Status:** ✅ Aktivno
