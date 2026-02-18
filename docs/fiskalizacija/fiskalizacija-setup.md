# Fiskalizacija – setup koraci

Ovaj dokument sažima operativne korake potrebne da nova fiskalizacijska infrastruktura proradi u okruženju.

## 1. Konfiguracija okoline

- Nadopuni `.env` datoteku vrijednostima iz `.env.example` za fiskalizaciju:

  ```env
  FISKAL_ENABLED=false
  FISKAL_ENV=demo
  FISKAL_CERT_PATH=certs/86058362621.F3.3.p12
  FISKAL_CERT_PASS=****************
  FISKAL_CA_PATH=
  FISKAL_TIMEOUT=10
  FISKAL_DEFAULT_SLIJED=P
  FISKAL_MAX_ATTEMPTS=3
  FISKAL_RETRY_BACKOFF=300
  FISKAL_LOG_CHANNEL=stack
  ```

- Dok ne dođu produkcijski certifikati, koristimo demo `.p12` koji se već nalazi u `certs/` direktoriju.
- Za svaku udrugu ćemo kasnije postaviti vlastite certifikate kroz kolone `fiskal_cert_path` i `fiskal_cert_pass` (tablica `udrugas`).

## 2. Migracije baze

1. Sigurnosno kopiraj bazu ili radi na lokalnoj kopiji.
1. Pokreni migracije:

  ```bash
  php artisan migrate
  ```

  Migracije dodaju:

- spremnik za fiskalne brojeve (`fiskalni_brojevi`)
- nove fiskalne kolone na tablicama `racuns`, `udrugas`, `lokacijas`
- POS metapodatke: `pos_terminali`, `pos_terminal_kanali` + proširenja tablica `transakcije` i `racuns` s vezom na POS terminal

1. Nakon migracija provjeri da su nove kolone popunjene (barem privremenim vrijednostima) kako bi generator imao podatke.

## 3. Popunjavanje podataka

- `udrugas`
  - `u_sustavu_pdv` postavi na `0` (sve udruge su izvan PDV-a).
  - `fiskal_cert_path` i `fiskal_cert_pass` ostavi prazno dok certovi ne stignu; demo cert koristimo kroz `.env`.
- `lokacijas`
  - `oznaka_poslovnog_prostora` i `oznaka_naplatnog_uredaja` ispuni privremenim oznakama (npr. `ZG1`, `KASA1`).
  - Po potrebi kasnije uskladi sa službenim oznakama iz FINA prijave.

## 4. Konfiguracija POS terminala i kanala

### 4.1 Podaci koje treba prikupiti

- `tip_terminala`: `classic_pos` (fizički terminal bez REST-a), `smart_pos` (Android/REST), `gateway` (bez uređaja, čista online naplata)
- `provider`: naziv banke ili PSP-a (npr. `wspay`, `monri`, `erste-smartpos`, `stripe`)
- `oznaka_naplatnog_uredaja`: službena oznaka iz FINA prijave (ako postoji)
- Serijski broj i model uređaja radi lakšeg servisnog praćenja
- Informacija koristi li lokacija više kanala (REST endpoint, lokalni agent, USB)

### 4.2 Popunjavanje tablice `pos_terminali`

- `udruga_id` i `lokacija_id` obavezno vežu terminal na organizacijsku jedinicu
- `driver_klasa` postavi na planirani driver, npr. `App\\Services\\Placanja\\Driveri\\SmartPosDriver`
- `konfiguracija` (JSON) služi za postavljanje zajedničkih parametara poput valute, default timeouta ili oznake blagajne
- `metadata` koristi za podatke koje moramo prikazivati operativnom timu (kontakt servisera, lokacija uređaja u prostoru)

Primjer zapisa (`konfiguracija` kolona):

```json
{
  "valuta": "HRK",
  "default_timeout": 45,
  "lokalna_kasa": "KASA1"
}
```

### 4.3 Popunjavanje tablice `pos_terminal_kanali`

- Svaki terminal može imati jedan ili više kanala; jedan mora imati `is_default=true`
- `tip_komunikacije`: `https`, `local_service`, `serial`
- `endpoint` i `port` / `putanja` popunjavaju se ovisno o tipu komunikacije
- `konfiguracija` (JSON) pohranjuje specifične parametre kanala (API key, timeout, kom port)
- `auth_podaci` (JSON) služi za tajne podatke koje kasnije možemo enkriptirati (client_id, client_secret, API token)

Primjer REST kanala (`tip_komunikacije=https`):

```json
{
  "base_url": "https://api.monri.com/v2",
  "headers": {
    "X-Auth-Token": "{{MONRI_TOKEN}}"
  }
}
```

Primjer lokalnog kanala (`tip_komunikacije=local_service`):

```json
{
  "host": "127.0.0.1",
  "port": 8081,
  "path_charge": "/payment/start",
  "path_status": "/payment/status"
}
```

### 4.4 Mapiranje drivera

- `classic_pos`: koristi lokalni agent i driver `App\\Services\\Placanja\\Driveri\\LokalniPosDriver`
- `smart_pos`: koristi REST driver `App\\Services\\Placanja\\Driveri\\SmartPosDriver`
- `gateway`: vodi se kroz `App\\Services\\Placanja\\Driveri\\OnlineGatewayDriver`

Driveri će u sljedećoj fazi čitati `konfiguracija` i `auth_podaci` te birati odgovarajući kanal.

## 5. Omogućavanje fiskalizacije

- Kada demo testiranje bude spremno, u `.env` promijeni `FISKAL_ENABLED=true`.
- Za produkciju se prebaci `FISKAL_ENV=prod` i postavi produkcijski certifikat po udruzi.
- `FinaClient` trenutno vraća "skipped" rezultat dok je `FISKAL_ENABLED=false`; stvarni SOAP poziv aktivira se automatski nakon uključivanja.

## 6. Dodatni koraci (naknadno)

- Parsiranje stvarnog SOAP odgovora (JIR) i validacija XSD-a.
- Izrada retry job-a i sučelja za pregled `fiskalni_logs` zapisa.
- Livewire 3 komponenta za vođenje korisnika kroz kreiranje narudžbe/računa.
- Automatske notifikacije ako fiskalizacija padne (e-mail/Slack, ovisno o dogovoru).
- Popis POS terminala i komunikacijskih kanala po lokaciji (tablice `pos_terminali`, `pos_terminal_kanali`) te sinkronizacija stvarnog stanja s konfiguracijom.
- Dogovoriti naming convention za `tip_terminala`, `tip_komunikacije` i `driver_klasa` te ga dokumentirati u README-u modula.

## 7. Testiranje

- Lokalno: kreiraj račun kroz postojeći flow (`PlacanjeFlow`) i provjeri jesu li polja `zki`, `jir`, `fiskaliziran_u` i `fiskalni_logs` ispunjena (u demo modu `jir` će ostati null, status `skipped`).
- Demo okruženje: nakon što dobijemo potvrđene oznake i certifikate, izvrši račun s `FISKAL_ENABLED=true` i provjeri FINA odgovor.

---

**Napomena:** trenutna implementacija je spremna za demo/testiranje. Jednom kad stignu službeni podaci od knjigovodstva i voditelja udruga (certifikati, oznake POS prostora/naplatnih uređaja), potrebno je popuniti tablice i ponovno pokrenuti fiskalizaciju za nove račune. Postojeći računi (nastali prije aktivacije) ostaju nefiskalizirani dok se ručno ne obrade kroz retry mehanizam koji slijedi u sljedećoj fazi.
