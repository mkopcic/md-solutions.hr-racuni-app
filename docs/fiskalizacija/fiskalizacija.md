# Fiskalizacija - stanje i plan

## Kontekst

- Nova aplikacija u Laravelu 12 zamjenjuje staru CI3 implementaciju; dokumentacija iz `docs/generiranje_racuna.md` vise nije relevantna.
- Fiskalizacija je do sada bila stub (dummy) implementacija u `App\Services\Fiskalizacija` koja generira nasumicne ZKI/JIR vrijednosti i ne salje zahtjeve prema FINA servisu.
- Dobiven je demo soft certifikat (FISKAL 3) smjesten u `certs/86058362621.F3.3.p12`. Lozinka ce se ucitavati iz `.env` postavki.
- Cilj je zavrsiti fiskalizaciju za sve racune (clanski i poslovni), ukljucujuci rucno ponovno slanje i audit logove.

## Trenutni status

- [x] Servisni sloj (`FiskalizacijaService`, `FinaClient`, `ZkiGenerator`, `XmlRequestBuilder`, `XmlSigner`, `FiskalContextResolver`) je implementiran i spojen na `PlacanjeFlow`.
- [x] Migracije dodaju fiskalne kolone (`oznaka_slijeda`, `oznaka_poslovnog_prostora`, `oznaka_naplatnog_uredaja`, `izdan_u`, `u_sustavu_pdv`, cert credentialsi) te tablicu brojcanika (`fiskalni_brojevi`).
- [x] `config/fiskalizacija.php` i `.env` kljucevi su dostupni; demo cert i payment kodovi mapirani.
- [x] Seederi popunjavaju minimalni demo set (udruge, lokacije, POS terminali i kanali, clanovi, narudzbe, treninzi) i izvjestaj na kraju obuhvaca nove metrike.
- [x] Livewire 3 wizard za kreiranje narudzbe/placanja (`/narudzbe/create`) spojen je na `PlacanjeFlow`, uključujući POS validacije i demo fiskalizaciju.
- [x] Queue job `FiskalizirajRacunJob` i retry job `RetryFiskalizacijaJob` postavljeni su za automatsku fiskalizaciju i ponovna slanja.

### Stanje 17.11.2025

- `php artisan fiskal:request:minimal --store=storage/app/fiskalizacija/manual/test --send --uredaj=K1` sada generira APIS/FINA-valjan XML, potpisuje ga, šalje i vraća JIR (`f9b7ec72-142d-4e39-94b5-14118c9d3184` u zadnjem testu). Artefakti (unsigned/signed XML, SOAP envelope, probe logovi, meta.json, cert info) nalaze se u navedenom direktoriju.
- `php artisan fiskal:send {racun_id} --force` uspješno fiskalizira stvarne račune; potvrđeno za ID 17, 18 i 19 (JIR-ovi se bilježe u tablici `racuni` i u `fiskalni_logs`). Ako želiš asinkrono slanje, pokreni radnika s `php artisan queue:work --queue=fiskalizacija` i dodaj `--queue` opciju komandi.
- `FiskalContextResolver` uvijek uzima OIB iz certifikata i sanitizira `OznNapUr` na samo znamenke, tako da i Livewire 3 wizard i CLI alati prolaze istu validaciju koju traži APIS IT.
- `fiskalni_logs` sada prihvaća i zapise bez `racun_id` (manualni minimal zahtjevi) pa svi testovi ostaju auditabilni.

## Postojeca arhitektura

- `App\Services\Placanja\PlacanjeFlow` kreira narudžbu, izvršava plaćanje, kreira transakciju i zatim stvara račun te zakazuje `FiskalizirajRacunJob` (status se u UI prikazuje kao "u obradi" dok job ne završi).
- `App\Jobs\FiskalizirajRacunJob` sinkrono ili asinhrono poziva `FiskalizacijaService`, a `RetryFiskalizacijaJob` svakih 10 minuta ponovno pokušava račune bez JIR-a.
- Listeners (`LogPaymentSuccessful`, `LogPaymentFailed`, `LogRacunFiskaliziran`, `LogFiskalizacijaFailed`) trenutno samo logiraju događaje, ali služe kao hook za buduće notifikacije.
- `App\Services\Fiskalizacija\FiskalizacijaService` više ne vraća dummy vrijednosti – proizvodi stvarni XML, digitalno ga potpisuje (`XmlSigner` koristi `robrichards/xmlseclibs`), šalje ga prema FINA endpointu (ovisno o `FISKAL_ENV`) i baca grešku ako FINA vrati status `failed`.
- `App\Models\Racuni\Racun` sadrzi osnovne kolone, ali nedostaju podaci potrebni za fiskalizaciju (oznaka slijeda, oznake poslovnog prostora i naplatnog uredaja, vrijeme izdavanja, PDV status...). Isto vrijedi za `PoslovniRacunDetalji`.
- `App\Models\Racuni\FiskalniLog` i migracija `2025_09_11_000004_create_fiskalni_logs_table.php` aktivno se koriste – svaki pokušaj fiskalizacije sprema `request_xml`, `response_xml`, status i parsirane FINA poruke.
- Udruga (`App\Models\Organizacijski\Udruga`) vec ima OIB; Lokacija (`App\Models\Organizacijski\Lokacija`) ima kolonu `lokacija_kasa` i dodatne oznake koje se mapiraju na oznaku naplatnog uredaja.

## Konfiguracija i okolina

- Certifikat: `certs/86058362621.F3.3.p12` (demo). U produkciji ce se ruta zamijeniti stvarnim certifikatom.
- `.env` kljucevi koje cemo uvesti:
  - `FISKAL_ENABLED=true|false` (globalni prekidac, demo okolina ce biti true).
  - `FISKAL_ENV=demo|prod` (odredjuje endpoint: demo `https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest` / produkcija `https://cis.porezna-uprava.hr:8449/FiskalizacijaService`).
  - `FISKAL_CERT_PATH=certs/86058362621.F3.3.p12` (relativno na project root).
  - `FISKAL_CERT_PASS=<lozinka>`.
  - `FISKAL_CA_PATH=` (opcionalno, ako bude trebalo specificirati CA bundle).
  - `FISKAL_TIMEOUT=10` (sekunde, podesivo).
- Implementirati `config/fiskalizacija.php` koji ucitava gore navedene vrijednosti i definira mape (npr. payment codovi, default oznake).

### Referentna dokumentacija

- Lokalne upute: [Fiskalizacija - Tehnicka specifikacija za korisnike v2.3 (PDF)](Fiskalizacija%20-%20Tehnicka%20specifikacija%20za%20korisnike_v2.3.pdf) – datoteka se nalazi u `docs_new/`.
- FINA demo WSDL: `https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest?wsdl` (sluzi za provjeru metoda poput `RacunZahtjev`). HTTP 405 na GET zahtjev je očekivan; bitno je da TLS i autentikacija prođu.
- FINA produkcijski WSDL: `https://cis.porezna-uprava.hr:8449/FiskalizacijaService?wsdl`.
- Opce upute i novosti: <https://www.porezna-uprava.hr/HR_Fiskalizacija/Stranice/Primjena-fiskalizacije.aspx>.

## Podatkovni zahtjevi

Minimalni set polja koje moramo imati za svaki fiskalizirani dokument (racun):

| Podatak (FINA naziv) | Izvor / planirano polje | Status |
| --- | --- | --- |
| `OIB` obveznika | `udruga.udruga_oib` | POSTOJI (potrebno osigurati da je popunjeno) |
| `OznSlijed` | nova kolona na racunu (`oznaka_slijeda`) | DODATI (vrijednosti `P` ili `N`) |
| `OznPosPr` | nova kolona na lokaciji (`oznaka_poslovnog_prostora`) ili mapirati iz postojece strukture | DODATI |
| `OznNapUr` | vec imamo `lokacija_kasa`, mozemo preimenovati ili duplicirati u novu kolonu za jasnocu | PROVJERITI/DODATI |
| Broj racuna (`BrOznRac`) | kombinacija oznake naplatnog uredaja + Godina + serijski broj | TREBA DEFINIRATI pravila |
| Datum/vrijeme izdavanja | nova kolona `izdan_u` (timestamp) | DODATI |
| `USustPdv` | flag na udruzi (`u_sustavu_pdv` bool) | DODATI |
| Nacini placanja | mapiranje `nacin_placanjas` na FINA oznake (`G`, `K`, `C`, `T`, `O`) | DODATI mapu |
| Pojedine stavke | za clanske racune se tretira kao jedna stavka (paket). Za poslovne racune moze biti vise stavki iz `PoslovniRacunDetalji` | TREBA RAZRADITI |
| NPT (neporezni dio), PDV stope | izracun po paketu/stavci | TREBA RAZRADITI |

Napomena oko pitanja "normalni slijed": `OznSlijed` je parametar u fiskalizaciji koji definira da li se koristi jedan opci brojcanik za sve naplatne uredaje (`P` - "Pojedinacni" / uobicajeni slijed) ili se koristi zasebni brojcanik po naplatnom uredaju (`N`). U praksi gotovo svi koriste `P`. Ako se koristi `P`, brojevi racuna se vode centralizirano i jedinstveno, ali svejedno mozemo sloziti broj po lokaciji ako to poslovno treba (uz uvjet da strogo cuvamo redoslijed). Ako zelite zasebnu numeraciju po kasi, moguce je koristiti `N`, ali tada treba dokazati da je to opravdano. Za sada cemo predloziti `P` (normalni slijed).

`potvrda_broj` se trenutno puni `R<uniqid>`. To nije prilagodjeno za zakonski broj racuna. Plan:

1. Uvesti servis koji generira broj racuna `BrojRacuna` u obliku `OznPosPr/OznNapUr/broj` za tekucu godinu.
2. `potvrda_broj` postaje taj broj nakon fiskalizacije (ili odmah pri kreiranju, ali prije slanja u FINA).
3. Koristimo baznu tablicu (npr. `fiskalni_brojevi`) ili atomicni counter po kombinaciji `OznPosPr+OznNapUr+Godina`.

## Ciljani workflow

1. **Kreiranje narudzbe** (nova Livewire 3 komponenta) → korisnik odabire clana/poslovnog korisnika, paket/stavke, nacin placanja.
2. **Placanje** (manualno ili putem PSP-a) → `PlacanjeFlow` kreira narudzbu, pokusava naplatiti.
3. **Kreiranje racuna** → generira se broj racuna i svi fiskalizacijski metapodaci (oznaka slijeda, oznake prostora, timestamp) te se spremaju na model.
4. **Fiskalizacija**:

    - Generiranje ZKI: konkatenacija OIB + vrijeme izdavanja + broj racuna + oznake + iznos; potpisivanje RSA-SHA1 privatnim kljucem iz certifikata; pretvaranje u uppercase hex string.
    - Generiranje `RacunZahtjev` XML-a (sa stavkama, PDV-om, nacinom placanja i opcionalnim dodatnim poljima).
    - XML digitalni potpis (XMLDSig) nad sadrzajem zahtjeva.
    - Slanje prema FINA SOAP endpointu (demo/prod) pomocu `SoapClient` ili `Guzzle` + `ext-soap`.
    - Parsiranje odgovora, citanje JIR-a i spremanje na racun.
    - Upis u `fiskalni_logs` (request, response, status, eventualna greska, vrijeme slanja, broj pokusaja).
    - Uspjesno: oznaciti racun kao fiskaliziran (`fiskaliziran_u` timestamp, `jir`, `zki`).
    - Neuspjesno: postaviti status `failed`, sacuvati poruku, ponuditi retry.

5. **Notifikacije/aktivnosti**: observer moze evidentirati promjene, ali treba paziti da ne salje email dok se fiskalizacija izvrsava batch-om.
6. **Retry mehanizam**: job/command koji cita `fiskalni_logs` sa statusom `failed` ili `retry` i ponovno pokusava slanje.

## Potrebne nadogradnje

1. **Konfig i helperi (dovrseno)**

- `config/fiskalizacija.php` i `.env` kljucevi postoje.
- Implementirani helperi: `BrojRacunaGenerator`, `FiskalContextResolver`, `ZkiGenerator`, `XmlRequestBuilder`, `FinaClient`.

1. **Migracije (dovrseno)**

- `racuns`, `udrugas`, `lokacijas` i `fiskalni_brojevi` imaju potrebne kolone.
- Preostaje procijeniti treba li dodatna parent tablica za poslovne racune.

1. **Servisi (status)**

- Retry job i napredni parser JIR poruka jos treba napisati.
- XMLDSig potpis implementiran je putem `XmlSigner` klase (koristi `robrichards/xmlseclibs`), a `FinaClient` parsira i logira sve FINA poruke (npr. `s006 - Sistemska pogreška prilikom obrade zahtjeva.`).

1. **Model mapping (djelomicno)**

- `FiskalContextResolver` popunjava oznake i brojeve; potrebno je prosiriti mapiranje nacina placanja (slug/oznaka) kako bi svi PSP-ovi imali tocan FIN kod.

1. **Poslovni racuni (otvoreno pitanje)**

- Ako ostaju na `racuns` modelu, treba prosiriti `XmlRequestBuilder` na vise stavki iz `PoslovniRacunDetalji`.

1. **UI i alati (pred nama)**

- Livewire komponenta za narudzbu/racun implementirana je u `app/Livewire/Placanja/CreateNarudzbaForm.php`, a u planu je dodatni UI za `FiskalniLog` retry i pregled.

## Odgovori na otvorena pitanja

- **Certifikat / lokacija**: potvrdeno; koristimo datoteku iz `certs/`. Lozinku necemo zapisivati u repozitorij.
- **Oznaka slijeda (normalni slijed)**: predlozit cemo `P` (pojedinacni, standardni slijed). Ako zelite zasebnu numeraciju po kasi, to znaci `N` i zahtjeva dodatnu logiku i registraciju u Poreznu upravu.
- **`potvrda_broj`**: postace polje gdje smjestamo zakonski broj racuna (po sabloni `OznPosPr/OznNapUr/Serija`). ID ostaje primarni kljuc. Dodat cemo tablicu/servis za generator.
- **Poslovni racuni**: ukljucit cemo ih u isti sustav fiskalizacije.

## Todo lista (prioritetno)

1. Zavrsiti XMLDSig potpis i obradu realnih SOAP odgovora (JIR, greske).
2. Implementirati retry job/command i UI za pregled `FiskalniLog` zapisa.
3. Izgraditi novu Livewire 3 komponentu za kreiranje narudzbe i racuna (gotovina, kartica/POS, online gateway).
4. Prosiriti mape nacina placanja i poslovnog racuna/stavki za vise artikala.
5. Pokriti servisne klase unit testovima (ZKI, broj racuna, XML mapping) i dopuniti README za deployment.

## Plan testiranja

- Napraviti testni `.env.demo` profil sa demo certifikatom i endpointom.
- Napisati "happy path" feature test: kreiranje racuna -> fiskalizacija -> zapis JIR/ZKI.
- Testovi za error scenarije (pogresna lozinka certifikata, timeout, validacijske greske XML-a).
- Manualni test: poslati zahtjev na demo endpoint i provjeriti odgovor (JIR) i logove.

### Manualni demo test (CLI)

- **Artisan dijagnostika**: koristi `php artisan fiskal:diagnostics` kako bi aplikacija sama napravila WSDL i Echo test preko konfiguriranog certifikata. Opcije poput `--operation=echo`, `--message=ping`, `--store=storage/fiskal-tests` i `--include-docs` pomažu pri spremanju rezultata i dokumentacije.

1. Postavi varijable u `.env`:

  ```dotenv
  FISKAL_ENABLED=true
  FISKAL_ENV=demo
  FISKAL_CERT_PATH=certs/86058362621.F3.3.p12
  FISKAL_CERT_PASS="<demo-lozinka>"
  ```

1. Kreiraj ili pronadi racun bez JIR-a (npr. koristeci Livewire wizard ili seeder) i zapisi njegov ID.
1. Pokreni worker: `php artisan queue:work --queue=fiskalizacija`. Ostavi ga aktivnim u zasebnom terminalu (za pojedinačno pokretanje koristi `--once`).
1. Pokreni test fiskalizacije: `php artisan fiskal:send {racun_id}`. Za forsiranje kada je servis onemogucen koristi `--force`, a za enqueanje koristi `--queue`.
1. Provjeri tablicu `fiskalni_logs` (kolone `request_xml`, `response_xml`, `status`, `error_message`) i da su `racuni.jir` i `racuni.zki` popunjeni. Parsirani kodovi grešaka (npr. `s006`) upisuju se u `error_message` i u `storage/logs/laravel.log`.
1. U slucaju greske pogledaj `storage/logs/laravel.log` te po potrebi ponovno pokreni `php artisan queue:work --queue=fiskalizacija`.

#### Ručna provjera konekcije (curl)

- Pretvori P12 u PEM s `openssl pkcs12 -legacy ... -out certs/86058362621.F3.3.pem -nodes` kako bi dobio kombinirani certifikat i ključ. Lozinka je jednaka onoj koju je FINA dodijelila pri izdavanju.
- Iz TLS handshaka preuzmi FINA Demo CA 2020 (issuer serverovog certifikata) i spremi ju u `certs/fina-demo-ca-2020.pem`. Bez ovog koraka curl/Guzzle ne mogu validirati serverovu stranu.
- Jednostavan GET na WSDL potvrđuje da TLS i klijent-ski cert rade (isti handshake sada automatski radi i `fiskal:request:minimal --send`, a rezultat sprema u `endpoint-probe.json`):

  ```bash
  curl https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest?wsdl \
    --cert certs/86058362621.F3.3.pem \
    --key certs/86058362621.F3.3.pem \
    --cacert certs/fina-demo-ca-2020.pem \
    --max-time 15 -v
  ```

  Response `HTTP/1.1 405 Method Not Allowed` + SOAP Fault znači da je TLS prošao, ali je metoda bila `GET` (FINA demo endpoint ne podržava GET). Za stvarni test pošalji SOAP `POST` (npr. echo poruku) kao u `docs_new/fiskalizacija-setup.md`; isti rezultat očekuj i u `endpoint-probe.json` kada koristiš minimalnu komandu.

### Operativna dijagnostika i logovi

- **Artisan dijagnostika:** `php artisan fiskal:diagnostic` (ili `fiskal:diagnostics`) radi WSDL i SOAP echo test s trenutno konfiguriranim certifikatom. Artefakti se spremaju u `storage/app/fiskalizacija/diagnostics/<timestamp>` (request, response, log). WSDL poziv vraća 405, dok uspješan echo (`status=200`) potvrđuje TLS i certifikat.
- **Minimalni zahtjev:** `php artisan fiskal:request:minimal --send` generira minimalni `RacunZahtjev` (bez oslanjanja na bazu), zapisuje ZKI i sve artefakte sprema u `storage/app/fiskalizacija/manual/<timestamp>`. Uz XML datoteke, komanda dodaje i:
  - `meta.json` s oznakama, iznosom, aktivnim endpointom/timeoutom i apsolutnim putanjama do cert i CA datoteka,
  - `certificate-info.json` sa subjectom, issuerom, rokom važenja i SHA1/SHA256 fingerprintovima učitanog certifikata,
  - `endpoint-probe.json` koji dokumentira automatski GET na WSDL (405 je očekivan i služi dokazivanju TLS konekcije prema APIS/FINA podršci),
  - `response-meta.json` s JIR-om ili popisom FINA grešaka te ugrađenim sadržajem `endpoint-probe.json`.
  Paket je spreman za eskalaciju bez dodatnih ručnih curl koraka.
- **Queue jobs:** `php artisan queue:work --queue=fiskalizacija --once` pokreće pojedinačni pokušaj. Neuspješni jobovi mogu se vratiti na red s `php artisan queue:retry all`; trenutno se sve greške evidentiraju u `fiskalni_logs` i Laravel logu.
- **Baza:** tablica `fiskalni_logs` čuva punu `request_xml`/`response_xml` i zadnju FINA poruku u `error_message`. Kod `s006` označava sistemsku grešku na FINA strani – provjeriti registrirane oznake i certifikat te eskalirati prema FINA podršci.
- **Logovi:** detaljni stack trace nalazi se u `storage/logs/laravel.log`. Za pregled kroz web sučelje koristi `log-viewer` (ruta `/log-viewer`).

### FINA endpointi

- Demo okolina: `https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest`
- Produkcija: `https://cis.porezna-uprava.hr:8449/FiskalizacijaService`

Endpointi se citaju iz `config/fiskalizacija.php` preko `FISKAL_ENV` (`demo` ili `prod`). Ako FINA objavi nove URL-ove, azuriraj konfiguraciju i `.env`.

## Sljedeci koraci

- Nakon potvrde dokumenta, pocinjemo s migracijama i konfiguracijom.
- Zatim implementiramo servisni sloj i nove klase.
- Paralelno pripremamo Livewire komponentu za kreiranje narudzbe/racuna.
- Zavrsno testiranje u demo okruzenju prije prelaska na produkciju.

## Operativne napomene

- Aplikacija trenutno pokriva tri sportske udruge; demo certifikat (FISKAL 3) je privremeno pohranjen lokalno i lozinku drzi developer.
- Svaka udruga mora do kraja godine ishoditi vlastiti FINA certifikat; arhitektura treba omoguciti pohranu certifikata i lozinki per udruga.
- Sve udruge posluju izvan sustava PDV-a (`USustPdv="N"`).
- Lokacije imaju svoje kase (naplatne uredaje); dok se ne potvrde oznake prostora/uredaja, koristimo privremene vrijednosti iz `lokacija_kasa`.

## Pitanja za knjigovodstvo i voditelje udruga

1. Tko je odgovoran za cuvanje i obnovu (rotaciju) .p12 certifikata svake udruge, gdje ce se fizicki nalaziti datoteka i tko posjeduje lozinku?
2. Koliko unaprijed treba zaprimiti obavijest o isteku certifikata i kome ju saljemo (knjigovodstvo, voditelj udruge)?
3. Koje su tocne oznake poslovnih prostora (`OznPosPr`) i naplatnih uredaja (`OznNapUr`) za svaku lokaciju/kasu; ima li lokacija s vise kasa ili mobilnih uredaja?
4. Potvrdjuju li udruge da zelite jedinstveni brojac (`OznSlijed="P"`) za sve kase, ili postoji potreba za zasebnim brojacima po kasi (`OznSlijed="N"`)?
5. Kojim formatom broja racuna treba ovjeriti ispis (npr. `OznPosPr/OznNapUr/000123`) i postoji li zahtjev za dodatnim prefiksom ili sufiksom?
6. Trebamo li na racunu prikazivati pojedinacne stavke (clanarine + dodatne usluge) ili je prihvatljiv zbrojeni prikaz po racunu?
7. Postoji li ijedna stavka koja ulazi u PDV ili su sve usluge oslobođene; treba li voditi dodatne oznake (npr. clanski doprinos vs. roba)?
8. Koji je dogovoreni postupak kad fiskalizacija trenutno nije dostupna (offline racun, naknadno slanje) i tko je odgovoran za provjeru uspjesnosti naknadnog slanja?
9. Treba li automatski slati obavijest (email/slack) knjigovodstvu ili voditelju ako fiskalizacija ne uspije i ostane u statusu `failed`?
