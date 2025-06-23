# Računi Obrt - Aplikacija za upravljanje računima

Aplikacija za izradu i upravljanje računima za obrtnike bazirana na Laravel 12 i Livewire 3 frameworku.

## Funkcionalnosti

### Kupci (Customers)

- Pregled svih kupaca
- Dodavanje novih kupaca
- Uređivanje postojećih kupaca
- Brisanje kupaca
- Pretraživanje kupaca po nazivu ili OIB-u

### Računi (Invoices)

- Pregled svih računa s filtriranjem po:
  - Statusu (plaćeni, neplaćeni, dospjeli)
  - Vremenskom razdoblju
  - Kupcu
- Izrada novih računa s dodavanjem stavki
- Pregled detalja računa
- Evidencija plaćanja (gotovina/transakcija)
- Generiranje PDF računa
- Brisanje računa

### Knjiga prometa (KPR)

- Pregled svih KPR zapisa po mjesecima i godinama
- Automatsko generiranje KPR zapisa iz računa
- Prikaz ukupnog mjesečnog i godišnjeg prometa
- Brisanje KPR unosa

### Porezni razredi (Tax Brackets)

- Pregled poreznih razreda za paušalno oporezivanje
- Dodavanje novih poreznih razreda
- Uređivanje postojećih poreznih razreda
- Brisanje poreznih razreda

### Podaci o obrtu (Business Settings)

- Unos i ažuriranje podataka o obrtu:
  - Naziv
  - OIB
  - Adresa
  - Kontakt podaci
  - Bankovni račun

### Pregled logova (Log Viewer)

- Pristup detaljnom pregledu aplikacijskih logova putem sučelja
- Filtriranje logova po razini (error, warning, info, itd.)
- Pretraga logova po sadržaju
- Pregled logova po datumima
- Dostupno samo administratorima na ruti `/logs`

### Ostale funkcionalnosti

- Dashboard sa statistikama
- Generiranje PDF računa
- Evidencija plaćanja
- Prilagođeno hrvatskom jeziku i valuti (EUR)

## Tehnologije

- Laravel 12
- Livewire 3
- TailwindCSS
- Alpine.js
- DomPDF za generiranje PDF-a

## Instalacija

```bash
# Klonirati repozitorij
git clone https://github.com/yourusername/racuni-obrt.git

# Instalirati dependency-je
composer install
npm install
npm run build

# Kopirati .env.example i postaviti konfiguraciju
cp .env.example .env

# Generirati app key
php artisan key:generate

# Pokrenuti migracije i seedere
php artisan migrate --seed

# Pokrenuti aplikaciju
php artisan serve
```

## Struktura baze podataka

### Businesses (Obrti)

- Informacije o obrtu: naziv, OIB, adresa, kontakt, bankovni račun

### Customers (Kupci)

- Podaci o kupcima: naziv, OIB, adresa, kontakt

### Invoices (Računi)

- Osnovni podaci računa: broj, datum, kupac, ukupni iznos
- Status plaćanja, datum plaćanja
- Veza na stavke računa

### InvoiceItems (Stavke računa)

- Pojedinačne stavke računa: naziv, količina, cijena, popust

### KprEntries (Knjiga prometa)

- Zapisi knjige prometa: mjesec, godina, iznos
- Veza na račun

### TaxBrackets (Porezni razredi)

- Paušalni porezni razredi: raspon prihoda, porezna osnovica, iznos poreza
