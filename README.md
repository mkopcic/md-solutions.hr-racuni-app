# 📊 Računi Obrt - Aplikacija za upravljanje računima

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind%20CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=black)](https://alpinejs.dev)

Aplikacija za izradu i upravljanje računima za obrtnike bazirana na Laravel 12 i Livewire 3 frameworku.

## 🔗 Demo

- Live demo: [https://7431-89-164-217-144.ngrok-free.app/](https://7431-89-164-217-144.ngrok-free.app/)

## 📋 Funkcionalnosti

### 👥 Kupci (Customers)

- 📋 Pregled svih kupaca
- ➕ Dodavanje novih kupaca kroz native HTML5 dialog modala
- ✏️ Uređivanje postojećih kupaca
- 🗑️ Brisanje kupaca
- 🔍 Pretraživanje kupaca po nazivu ili OIB-u
- 🎨 Sve akcije koriste Font Awesome ikone

### 💼 Računi (Invoices)

- 📊 Pregled svih računa s filtriranjem po:
  - 🔴🟢 Statusu (plaćeni, neplaćeni, dospjeli)
  - 📅 Vremenskom razdoblju
  - 👤 Kupcu
- ✨ Izrada novih računa s dodavanjem stavki
- 🏷️ **Tipovi računa:** SPO, AMK, FCZ, SFL, WDR (vlastite sekvence brojanja)
- 🔢 **Automatsko brojanje:** Format `broj/mjesec/1/tip` (npr. 1/1/1/SPO)
- 💳 **Način plaćanja:** Virman, Gotovina, Kartica
- 📐 **Jedinice mjere:** kom (komada), sat (sati), dan (dani)
- 💹 **PDV kalkulacija:** Automatski izračun po stavkama sa različitim stopama (25%, 13%, 5%, 0%)
- 🔍 Brz odabir usluga iz dropdown-a prilikom kreiranja računa
- 📋 Pregled detalja računa
- 💰 Evidencija plaćanja (gotovina/transakcija)
- 📄 **Profesionalni PDF računi** sa plavom temom, QR kodom i detaljnom PDV razradom
- 🔲 **HUB3 QR kod** za jednostavno plaćanje putem mobilne banke
- 💾 Download PDF računa
- 🗑️ Brisanje računa
- 🎨 Koristi Font Awesome ikone za sve akcije

### 🛍️ Usluge (Services)

- 📋 Katalog predložaka usluga za brže kreiranje računa
- ➕ Dodavanje novih usluga s nazivom, cijenom i opisom
- ✏️ Uređivanje postojećih usluga
- 🗑️ Brisanje usluga
- 🔄 Brz odabir usluga iz dropdown-a prilikom kreiranja računa

### 📚 Knjiga prometa (KPR)

- 📈 Pregled svih KPR zapisa po mjesecima i godinama
- 🤖 Automatsko generiranje KPR zapisa iz računa
- 💹 Prikaz ukupnog mjesečnog i godišnjeg prometa
- 📝 Dodavanje opisa za svaki KPR zapis
- 🗑️ Brisanje KPR unosa

### 💸 Porezni razredi (Tax Brackets)

- 📊 Pregled poreznih razreda za paušalno oporezivanje
- ➕ Dodavanje novih poreznih razreda
- ✏️ Uređivanje postojećih poreznih razreda
- 🗑️ Brisanje poreznih razreda

### 🏢 Podaci o obrtu (Business Settings)

- 📝 Unos i ažuriranje podataka o obrtu:
  - 🏷️ Naziv
  - 🔢 OIB
  - 📍 Adresa
  - 📞 Kontakt podaci
  - 🏦 Bankovni račun

### 📋 Pregled logova (Log Viewer)

- 🔍 Pristup detaljnom pregledu aplikacijskih logova putem sučelja
- 🔖 Filtriranje logova po razini (error, warning, info, itd.)
- 🔎 Pretraga logova po sadržaju
- 📅 Pregled logova po datumima
- 🔒 Dostupno samo administratorima na ruti `/logs`

### ✨ Ostale funkcionalnosti

- 📊 Dashboard sa statistikama i brzim pristupom funkcijama
- 🎯 Visoko vidljiv "Novi račun" button na dashboardu s FA ikonom
- 📄 Napredni PDF sustav s inline pregledom i download opcijama
- 💰 Evidencija plaćanja kroz native dialog modala
- 🇭🇷 Prilagođeno hrvatskom jeziku i valuti (€)
- 🎨 Font Awesome ikone kroz cijelu aplikaciju
- 📱 Native HTML5 dialog elementi umjesto jQuery modala
- 🎨 Moderan UI/UX s TailwindCSS i Alpine.js

## 🛠️ Tehnologije

- ![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white) Laravel 12
- ![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white) Livewire 3
- ![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?logo=tailwind-css&logoColor=white) TailwindCSS
- ![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?logo=alpine.js&logoColor=black) Alpine.js
- ![FontAwesome](https://img.shields.io/badge/Font%20Awesome-6-528DD7?logo=font-awesome&logoColor=white) Font Awesome 6 ikone
- ![DomPDF](https://img.shields.io/badge/DomPDF-PDF-orange) DomPDF za generiranje PDF-a
- ![QRCode](https://img.shields.io/badge/Simple%20QR%20Code-4.2-green) Simple QR Code za HUB3 plaćanja
- ![HTML5](https://img.shields.io/badge/HTML5-Dialogs-E34F26?logo=html5&logoColor=white) Native HTML5 dialog elementi
- ![AI](https://img.shields.io/badge/Laravel-AI%20SDK-FF2D20?logo=openai&logoColor=white) Laravel AI SDK za AI integracije
- ![Boost](https://img.shields.io/badge/Laravel-Boost%202.0-FF2D20?logo=laravel&logoColor=white) Laravel Boost

## 🚀 Instalacija

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

# Objaviti Log Viewer assete
php artisan vendor:publish --tag=log-viewer-assets --force

# Pokrenuti aplikaciju
php artisan serve
```

## 📊 Struktura baze podataka

### 🏢 Businesses (Obrti)

- Informacije o obrtu: naziv, OIB, adresa, kontakt, bankovni račun

### Customers (Kupci)

- Podaci o kupcima: naziv, OIB, adresa, kontakt

### Invoices (Računi)

- Osnovni podaci računa: broj, datum, kupac, ukupni iznos
- Status plaćanja, datum plaćanja
- Veza na stavke računa
- **Nova polja:** invoice_number, invoice_year, invoice_type, payment_method, subtotal, tax_total

### InvoiceItems (Stavke računa)

- Pojedinačne stavke računa: naziv, količina, cijena, popust
- **Jedinica mjere:** kom/sat/dan
- **PDV kalkulacija:** tax_rate, tax_amount per stavka

### KprEntries (Knjiga prometa)

- Zapisi knjige prometa: mjesec, godina, iznos
- Opis transakcije za detaljnije praćenje
- Veza na račun

### TaxBrackets (Porezni razredi)

- Paušalni porezni razredi: raspon prihoda, porezna osnovica, iznos poreza

### Services (Usluge)

- Katalog predložaka usluga: naziv, cijena, opis
- Veza na stavke računa za brže kreiranje

## 🤖 Laravel AI SDK

Aplikacija koristi **Laravel AI SDK** za AI mogućnosti:

- 🧠 **Unified API** za rad s AI providerima (OpenAI, Anthropic, Gemini, xAI, Mistral, Ollama, itd.)
- 🤝 **AI Agenti** - specijalizirani asistenti za specifične zadatke
- 💾 **Conversation Memory** - automatska pohrana povijesti razgovora
- 🛠️ **Custom Tools** - vlastite funkcije koje AI može pozivati
- 🖼️ **Image Generation** - generiranje slika
- 🎤 **Text-to-Speech** - pretvorba teksta u govor
- 📝 **Transcription** - pretvorba govora u tekst
- 🔍 **Embeddings & Semantic Search** - vektorsko pretraživanje
- 📊 **Structured Output** - vraćanje JSON struktura umjesto teksta

📚 **Detaljnu dokumentaciju** vidi u [docs/LARAVEL_AI_SDK.md](docs/LARAVEL_AI_SDK.md)

## � Dokumentacija
### 🚀 Quick Start
- **[Quick Reference](docs/QUICK_REFERENCE.md)** - Brzi pregled novih funkcionalnosti i izmjena
### 📘 Korisnička Dokumentacija
- **[Korisnički Vodič](docs/USER_GUIDE.md)** - Potpune upute za korištenje aplikacije
  - Kreiranje računa korak po korak
  - Objašnjenje PDV kalkulacije
  - Kako koristiti QR kod za plaćanje
  - Često postavljana pitanja

### 📙 Tehnička Dokumentacija
- **[Instalacijski Vodič](docs/INSTALLATION.md)** - Setup, konfiguracija i struktura projekta
- **[PDF Redesign Changelog](docs/CHANGELOG_PDF_REDESIGN.md)** - Detaljne izmjene PDF sustava (16.02.2026)
  - Nova polja u bazi (invoice_number, tax_rate, payment_method, itd.)
  - QR kod implementacija (HUB3 standard)
  - Livewire komponente izmjene
  - Testiranje i rollback upute
- **[Laravel AI SDK](docs/LARAVEL_AI_SDK.md)** - AI integracije i mogućnosti

### 📄 Primjeri PDF Računa
- `docs/1_1_1_SPO.pdf` - SPO račun primjer
- `docs/2_1_1_AMK.pdf` - AMK račun primjer
- `docs/3_1_1_FCZ.pdf` - FCZ račun primjer
- `docs/4_1_1_SFL.pdf` - SFL račun primjer
- `docs/5_1_1_WDR.pdf` - WDR račun primjer

## �🚀 Laravel Boost

Aplikacija koristi **Laravel Boost v2.1.0** - MCP server koji pruža AI agentima:

- 📊 **application-info** - metapodaci o aplikaciji (verzije paketa, PHP, baza, modeli)
- 🛣️ **list-routes** - popis svih ruta u aplikaciji
- 📋 **list-artisan-commands** - dostupne Artisan naredbe
- 🗄️ **database-query** - direktno izvršavanje SQL upita
- 📐 **database-schema** - prikaz strukture baze
- 🔧 **tinker** - izvršavanje PHP koda za debugging
- 📄 **read-log-entries** - čitanje error logova
- 🌐 **get-absolute-url** - generiranje pravih URL-ova
- 📚 **search-docs** - semantička pretraga 17,000+ dokumenata Laravel ekosustava
- 🧪 **browser-logs** - pristup browser console logovima
- ⚙️ **get-config** - čitanje config vrijednosti
