# Fiskalizacija - stanje i plan

## Kontekst

- Nova aplikacija u Laravelu 12 zamjenjuje staru CI3 implementaciju; dokumentacija iz `docs/generiranje_racuna.md` vise nije relevantna.
- Fiskalizacija je do sada bila stub (dummy) implementacija u `App\Services\Fiskalizacija` koja generira nasumicne ZKI/JIR vrijednosti i ne salje zahtjeve prema FINA servisu.
- Dobiven je demo soft certifikat (FISKAL 3) smjesten u `certs/86058362621.F3.3.p12`. Lozinka ce se ucitavati iz `.env` postavki.
- Cilj je zavrsiti fiskalizaciju za sve racune (clanski i poslovni), ukljucujuci rucno ponovno slanje i audit logove.

## Postojeca arhitektura

- `App\Services\Placanja\PlacanjeFlow` kreira narudzbu, izvrsava placanje, kreira transakciju i zatim poziva `UslugaFiskalizacije::fiskaliziraj($racun)`.
- `App\Services\Fiskalizacija\UslugaFiskalizacije` trenutno:
  - generira testni XML kroz `GraditeljXMLa::generirajXML($racun)`;
  - dodjeljuje fiktivne `ZKI-<uniqid>` i `JIR-<uniqid>` vrijednosti;
  - upisuje ih na `racuns` tablicu i vraca podatke.
- `App\Services\Fiskalizacija\GraditeljXMLa` vraca staticki `<Racun>` XML bez potpisa i obaveznih polja.
- `App\Models\Racuni\Racun` sadrzi osnovne kolone, ali nedostaju podaci potrebni za fiskalizaciju (oznaka slijeda, oznake poslovnog prostora i naplatnog uredaja, vrijeme izdavanja, PDV status...). Isto vrijedi za `PoslovniRacunDetalji`.
- `App\Models\Racuni\FiskalniLog` i migracija `2025_09_11_000004_create_fiskalni_logs_table.php` su spremni za zapis request/response XML-a i statusa, ali se trenutno ne koriste.
- Udruga (`App\Models\Organizacijski\Udruga`) vec ima OIB; Lokacija (`App\Models\Organizacijski\Lokacija`) ima kolonu `lokacija_kasa` koja ce se mapirati na oznaku naplatnog uredaja.

## Konfiguracija i okolina

- Certifikat: `certs/86058362621.F3.3.p12` (demo). U produkciji ce se ruta zamijeniti stvarnim certifikatom.
- `.env` kljucevi koje cemo uvesti:
  - `FISKAL_ENABLED=true|false` (globalni prekidac, demo okolina ce biti true).
  - `FISKAL_ENV=demo|prod` (odredjuje endpoint: demo `https://cistest.apis-it.hr:8449/FiskalizacijaService` / produkcija `https://cis.porezna-uprava.hr:8449/FiskalizacijaService`).
  - `FISKAL_CERT_PATH=certs/86058362621.F3.3.p12` (relativno na project root).
  - `FISKAL_CERT_PASS=<lozinka>`.
  - `FISKAL_CA_PATH=` (opcionalno, ako bude trebalo specificirati CA bundle).
  - `FISKAL_TIMEOUT=10` (sekunde, podesivo).
- Implementirati `config/fiskalizacija.php` koji ucitava gore navedene vrijednosti i definira mape (npr. payment codovi, default oznake).

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

1. **Konfig i helperi**
   - Kreirati `config/fiskalizacija.php` i `.env` varijable.
   - Helper klase: `CertifikatLoader`, `XmlPotpisivac`, `ApiKlijent`, `ZkiGenerator`, `BrojRacunaGenerator`, `MapperPlacanja`.

2. **Migracije**
   - `racuns`:
     - `oznaka_slijeda` (char 1, default `P`).
     - `oznaka_poslovnog_prostora` (string 20).
     - `oznaka_naplatnog_uredaja` (string 20) — mozemo inicijalno prepopuniti iz `lokacija_kasa`.
     - `izdan_u` (timestamp) — vrijeme izdavanja racuna (obvezno za ZKI).
     - `ukupno_pdv` i `osnovica` ako trebaju za izvjestaje (opsionalno, moze se racunati u letu).
   - `poslovni_racun_detaljis`:
     - `oznaka_slijeda`, `oznaka_poslovnog_prostora`, `oznaka_naplatnog_uredaja`, `izdan_u` (ovisno o tome vodi li se fiskalizacija per dokumentu ili per stavci; vjerojatno ce trebati tablica `poslovni_racuni` koja je parent, treba procijeniti punt).
   - `udrugas`:
     - `u_sustavu_pdv` (boolean, default true/false prema business pravilima).
   - `lokacijas`:
     - `oznaka_poslovnog_prostora` (ako se ne koristi drugi izvor).
   - Opcionalno: tablica/kolone za brojcanike.

3. **Servisi**
   - Refaktor `UslugaFiskalizacije` u vise komponenti:
     - `FiskalizacijaService` (facade): orkestrira proces.
     - `XmlRequestBuilder` (generira sve elemente prema FINA XSD-u).
     - `XmlSigner` (radi XMLDSig potpis, canonicalization, prikljucivanje X509 certifikata).
     - `FinaClient` (SOAP poziv, handle SSL cert, CA, timeouts).
     - `ZkiCalculator` i `JirResponseParser`.
   - Implementirati logiku za `FiskalniLog` (create, update status, biljezi greske).

4. **Model mapping**
   - Prilikom kreiranja racuna povuci iz `udruga` i `lokacija` potrebne oznake.
   - Mapirati `nacin_placanja_id` -> `nacinPlacanja.fina_code` (novo polje ili tabela mapiranja).

5. **Poslovni racuni**
   - Odmah podrzati i poslovne racune (`PoslovniRacunDetalji`). Vjerojatno cemo uvesti parent model `PoslovniRacun` (ako ne postoji) kako bismo imali jedan fiskalizirani dokument, a `PoslovniRacunDetalji` budu stavke.
   - Ili, ako se poslovni racun generira iz istog `racuns` modela (treba potvrditi), osigurati da se sve stavke nalaze i u XML-u.

6. **UI i alati**
   - Nova Livewire 3 komponenta („Kreiranje narudzbe / racuna") koja vodi korisnika kroz:
     1. Odabir clana/poslovnog korisnika.
     2. Odabir paketa ili definiranje stavki.
     3. Odabir nacina placanja i potvrdu.
     4. Pregled i potvrdu fiskalnih oznaka (automatski popunjene ali vidljive).
     5. Zavrsni korak: kreiranje narudzbe + racuna + fiskalizacija.
   - Postojecu Livewire komponentu za kreiranje racuna backup-irati (copy u npr. `RacunFormLegacy.php`) prije uvodjenja nove.
   - Dodati admin alat za pregled `FiskalniLog` zapisa i rucno ponovno slanje.

## Odgovori na otvorena pitanja

- **Certifikat / lokacija**: potvrdeno; koristimo datoteku iz `certs/`. Lozinku necemo zapisivati u repozitorij.
- **Oznaka slijeda (normalni slijed)**: predlozit cemo `P` (pojedinacni, standardni slijed). Ako zelite zasebnu numeraciju po kasi, to znaci `N` i zahtjeva dodatnu logiku i registraciju u Poreznu upravu.
- **`potvrda_broj`**: postace polje gdje smjestamo zakonski broj racuna (po sabloni `OznPosPr/OznNapUr/Serija`). ID ostaje primarni kljuc. Dodat cemo tablicu/servis za generator.
- **Poslovni racuni**: ukljucit cemo ih u isti sustav fiskalizacije.

## Todo lista (prioritetno)

1. Pripremiti `.env` kljuceve i novi `config/fiskalizacija.php`.
2. Dodati migracije za nova polja (`racuns`, `udrugas`, `lokacijas`, eventualno tablica brojcanika i poslovni racuni).
3. Izgraditi servisne klase (cert, ZKI, XML, SOAP) + pisati unit testove za kriticne dijelove (ZKI kalkulacija, XML generiranje).
4. Implementirati logiku u `UslugaFiskalizacije` (ili novu facade klasu) i povezati sa `PlacanjeFlow`.
5. Upotpuniti `FiskalniLog` zapisivanje.
6. Dodati retry komandu/job i osnovni admin UI.
7. Refaktor / kreirati novu Livewire 3 komponentu za narudzbe i racune.
8. Dokumentirati proces testiranja (demo environment, sample racun, usporedba sa FINA emulatorom).

## Plan testiranja

- Napraviti testni `.env.demo` profil sa demo certifikatom i endpointom.
- Napisati "happy path" feature test: kreiranje racuna -> fiskalizacija -> zapis JIR/ZKI.
- Testovi za error scenarije (pogresna lozinka certifikata, timeout, validacijske greske XML-a).
- Manualni test: poslati zahtjev na demo endpoint i provjeriti odgovor (JIR) i logove.

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

