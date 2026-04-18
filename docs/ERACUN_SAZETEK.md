# e-Račun B2B - Sveukupni Sažetak Istraživanja

**Datum istraživanja:** 18. travnja 2026.  
**Aplikacija:** MD Solutions - Računi (Laravel 12)  
**OIB:** 86058362621  
**Certifikat:** `86058362621.A.4.p12` (Fina Demo CA 2020, validan do 31.07.2030.)

---

## 1. Trenutno stanje - Što nije radilo i zašto

### Greška u logovima (danas, 18.04.2026.)

Svaki pokušaj slanja/provjere statusa e-računa vraća:

```
HTTP 500 | SifraGreske: s006 | "Sistemska pogreška prilikom obrade zahtjeva"
```

Response XML je u **FISKALIZACIJA formatu** (referencira `FiskalizacijaSchema.xsd`), potpisan od servera `CN=fiskalcistest` - što jasno pokazuje da server ne zna ništa o e-Računima.

### 5 konkretnih razloga zašto ne radi

| # | Problem | Detalji |
|---|---------|---------|
| 1 | **Pogrešan endpoint** | Config koristi `cistest.apis-it.hr:8449/EracunServiceTest` - to je **fiskalizacijski** server, ne e-Račun server |
| 2 | **OIB nije registriran** | Certifikat postoji, ali OIB mora biti posebno registriran u e-Račun demo sustavu (pristupnica!) |
| 3 | **Nema WS-Security** | SOAP poruke imaju `<soapenv:Header/>` (prazan header) - FINA zahtijeva digitalni potpis SOAP headera |
| 4 | **Asinkroni model = bilateralan** | "Stari način" zahtijeva da i VI imate web servis koji prima asinkrone odgovore od FINA-e |
| 5 | **laravel.log = 359 MB** | Svaki neuspjeli poziv logira cijeli SOAP response s Base64 certifikatom - trebalo bi rotirati |

---

## 2. Što je ispravno napravljeno

- ✅ Demo aplikacijski certifikat dobiven od FINA-e (Fina Demo CA 2020)
- ✅ Certifikat uspješno ekstraktovan iz `.p12` u `.pem` format
- ✅ TLS konekcija s client certifikatom tehnički radi
- ✅ Arhitektura servisa u Laravelu (`EracunFina/`) je solidna osnova
- ✅ UBL 2.1 XML generator implementiran
- ✅ XMLDSig potpisivanje UBL dokumenta implementirano

---

## 3. FINA-ina infrastruktura - Što smo pronašli na internetu

### Testni endpointi (istraživanje)

| URL | DNS | TCP | Rezultat |
|-----|-----|-----|---------|
| `demo-eracun.fina.hr` | ❌ | N/A | Domain ne postoji |
| `ws-test.fina.hr` | ❌ | N/A | Domain ne postoji |
| `eracun.fina.hr` | ✅ | ❌ | TCP timeout (89.249.110.130) |
| `cistest.apis-it.hr:8449/EracunServiceTest` | ✅ | ✅ | Radi, ali vraća fiskalizacijski odgovor |

**Zaključak:** Pravi e-Račun demo endpoint **ne postoji javno** - dobiva se tek nakon registracije na demo okolinu.

### Greška 403 na `digitalneusluge.fina.hr/eRacunB2B`

Ovo je **portal za registrirane korisnike** e-Račun B2B sustava. 403 znači da OIB 86058362621 **nije registriran** u sustavu. Nije greška u certifikatu - nego nedostaje prijava/pristupnica.

---

## 4. Dva načina integracije - Usporedba

### Opcija A: Finin integracijski modul (PREPORUČENO) 🟢

```
Laravel App → REST/JSON → Finin modul (lokalno/server) → SOAP (potpisano) → FINA servis
```

**Prednosti:**
- ✅ **Besplatno** (FINA daje modul bez naknade)
- ✅ Ne trebate implementirati WS-Security
- ✅ Modul sam potpisuje SOAP poruke vašim certifikatom
- ✅ Podržava JSON ili XML format za slanje
- ✅ Ne trebate vlastiti web servis za asinkrone odgovore

**Zahtjev za modul:** Pošaljite na `eracun.itpodrska@fina.hr`

---

### Opcija B: Direktna SOAP integracija (trenutna implementacija) 🔴

```
Laravel App → SOAP (WS-Security potpis) → FINA servis → asinkroni odgovor → VAŠ web servis
```

**Problemi s trenutnom implementacijom:**
- ❌ Endpoint je pogrešan (fiskalizacijski server)
- ❌ Nedostaje WS-Security potpis SOAP headera (samo UBL je potpisan)
- ❌ "Asinkroni stari način" zahtijeva da i VI imate web servis koji prima povratne poruke od FINA-e (IP/URL treba dogovoriti s FINOM)
- ❌ Potreban poseban SSL poslužiteljski certifikat za vaš web servis
- ❌ Zahtijevan je serverski demo certifikat uz aplikacijski

**Alternativa unutar "starog načina":** Postoji i **sinkrona** verzija (polling - dohvaćate status sami) koja je jednostavnija od asinkrone.

---

## 5. Što morate napraviti - Konkretan akcijski plan

### Korak 1 - Registracija na demo okolinu (OBAVEZNO, bez ovoga ništa ne radi)

1. Preuzmite obrazac: https://www.fina.hr/ngsite/content/download/9166/164444/1
2. Ispunite **Pristupnicu za internetski servis Fina e-Račun**
3. **Ručno nadopišite "Demo"** na pristupnici
4. Pošaljite na: `eracun.itpodrska@fina.hr`
5. Priložite presliku osobne iskaznice skrbnika certifikata

**Nakon registracije:** FINA aktivira OIB u demo sustavu i šalje vam pristupne podatke.

---

### Korak 2A - Ako idete s integracijskim modulom (lakši put)

1. U istom mailu zatražite i **integracijski modul**
2. FINA šalje: modul (executable), uputstvo, REST API dokumentaciju
3. Pokrenete modul na serveru
4. Iz Laravela šaljete REST/JSON prema lokalnom modulu (npr. `http://localhost:8080/api`)
5. Modul sve ostalo rješava sam

---

### Korak 2B - Ako nastavljate sa SOAP integracijom (teži put)

Dodatno uz registraciju trebate:

1. **Odabrati sinkroni model** (polling) umjesto asinkronog - izbjegava potrebu za vlastitim web servisom
2. Implementirati **WS-Security** potpis SOAP headera (uz postojeći UBL XMLDSig)
3. Koristiti ispravne endpointe koje FINA dostavi nakon registracije
4. Nadograditi `FinaEracunClient.php` za WS-Security

---

### Korak 3 - Tehničke specifikacije za čitanje

FINA ima nekoliko vrsta specifikacija - ovo je relevantno:

| Specifikacija | URL |
|--------------|-----|
| Sinkrona obrada (preporučeno za start) | https://www.fina.hr/digitalizacija-poslovanja/e-racun/tehnicka-specifikacija/tehnicka-specifikacija-slanje-racuna-web-servisom |
| Asinkrona obrada (stari način - vaša trenutna implementacija) | https://www.fina.hr/digitalizacija-poslovanja/e-racun/tehnicka-specifikacija/tehnicka-specifikacija-za-spajanje-putem-asinkronih-web-servisa-stari-nacin-razmjene |
| Tehnička spec na engleskom | https://www.fina.hr/digitalizacija-poslovanja/e-racun/tehnicka-specifikacija/tehnicka-specifikacija-razmjena-podataka... |

---

## 6. Pregled grešaka u FINA-inoj tablici (za referencu)

Najvažnije greške koje FINA vraća pri odbijanju:

| Šifra | Opis | Tipičan uzrok |
|-------|------|--------------|
| `s006` | Sistemska pogreška | Pogrešan endpoint ili server ne prepoznaje zahtjev |
| `151` | Nepoznat ID poslovnog subjekta | OIB nije registriran u e-Račun sustavu |
| `152` | Pošiljatelj nema prava slanja | Certifikat nije autoriziran za taj OIB |
| `161` | Nema prava potpisa poruke | WS-Security certifikat ne odgovara OIB-u |
| `301` | Potpis e-računa nije valjan | XMLDSig na UBL dokumentu nije ispravan |
| `313` | Certifikat poništen | Certifikat je revociran |
| `315` | Pogrešan CA | Certifikat ne dolazi od FINA-inog CA |

---

## 7. Stanje certifikata

```
Naziv:      MD SOLUTIONS RAČUNI
OIB:        HR86058362621
Issuer:     Fina Demo CA 2020
Validan od: 26. veljača 2026.
Validan do: 31. srpanj 2030.
Lozinka:    K2wbNnwGuFT4X9 (pohranjena u .env kao ERACUN_DEMO_CERT_PASSWORD)

Lokacija:
  storage/certificates/86058362621.A.4.p12      ← originalni PKCS#12
  storage/certificates/86058362621.A.4.pem      ← cert + key kombinirano
  storage/certificates/86058362621.A.4-cert.pem ← samo certifikat
  storage/certificates/86058362621.A.4-key.pem  ← samo private key
  storage/certificates/fina-demo-ca-root.pem    ← FINA Demo CA root
```

---

## 8. Preporučeni sljedeći koraci (prioritetno)

1. **Odmah:** Poslati pristupnicu (+ zahtjev za modul) na `eracun.itpodrska@fina.hr`
2. **Odmah:** Rotirati/počistiti `storage/logs/laravel.log` (359 MB)
3. **Nakon odgovora FINA-e:** Testirati s integracijskim modulom (lakši put)
4. **Opcija:** Ako FINA odgovori s ispravnim endpointima, nadograditi `FinaEracunClient.php` za WS-Security

---

*Dokument kreiran: 18. travnja 2026. | Istraživanje: GitHub Copilot + FINA.hr dokumentacija*
