# Mail FINI - Pristupnica e-Račun B2B + Demo Endpoint

**Datum sastavljanja:** 20. travnja 2026.  
**Status:** 📝 Spreman za slanje

---

## Adresa primatelja

```
Prima:    eracun.itpodrska@fina.hr
Predmet:  Pristupnica e-Račun B2B - OIB 86058362621 - MD SOLUTIONS VL. MARINA KOPČIĆ
```

---

## Tekst maila

---

Poštovani,

obraćam vam se u vezi aktivacije e-Račun B2B usluge za naš obrt.

**Podaci o subjektu:**

| | |
|---|---|
| Naziv | MD SOLUTIONS VL. MARINA KOPČIĆ |
| OIB | 86058362621 |
| Adresa | K. F. ŠEFERA 29, 31431 ČEPIN |
| Skrbnik certifikata | KOPČIĆ MARIJAN |
| Kontakt email | mkopcic@gmail.com |

---

**Što smo napravili:**

Posjedujemo valjani demo aplikacijski certifikat izdan od strane FINA-e:

| | |
|---|---|
| Naziv certifikata | MD SOLUTIONS RAČUNI |
| Datoteka | 86058362621.A.4.p12 |
| Referentni broj | FFAB4A0D69B5D3E798CB |
| Issuer | Fina Demo CA 2020 |
| Validan od | 26. veljača 2026. |
| Validan do | 31. srpanj 2030. |
| SHA1 Fingerprint | 3E:C2:ED:05:A0:25:4C:6E:33:4B:2B:BE:47:E1:C5:D6:88:68:B8:DC |

Razvili smo Laravel aplikaciju za slanje e-računa koja:
- generira UBL 2.1 XML prema CIUS-HR-2025 specifikaciji
- potpisuje XML digitalno (XMLDSig, RSA-SHA256)
- potpisuje SOAP header WS-Security standardom (BinarySecurityToken)
- komunicira putem HTTPS s client certifikat autentifikacijom

Certifikat je testiran i radi — HTTPS TLS handshake s FINA-inim serverima uspješan je.

---

**Što nam je potrebno:**

Molimo vas za sljedeće:

1. **Aktivaciju OIB-a 86058362621 u demo e-Račun B2B sustavu**  
   (Pristupnica za internetski servis Fina e-Račun — demo okolina)

2. **Točan URL demo endpointa** za slanje e-računa (SOAP ili REST)  
   Endpointi koje smo pronašli u dokumentaciji ne postoje ili nisu dostupni (`demo-eracun.fina.hr`, `ws-test.fina.hr`).

3. **Potvrdu prihvatljivog načina integracije** — preferiramo sinkroni model (polling) ako je dostupan za demo, kako ne bismo morali postavljati vlastiti web servis za asinkrone odgovore.

4. **Ako je dostupan — Finin integracijski modul**  
   Za brže testiranje, ako vaš integracijski modul (middleware) može preuzeti SOAP/WS-Security komunikaciju.

---

**Tehnički stack:**

- Platforma: Laravel 12 / PHP 8.4.20 / Linux server
- SOAP klijent: Guzzle (direktni XML POST, bez SoapClient)
- XML Signing: `robrichards/xmlseclibs`
- UBL format: UBL 2.1, CustomizationID `urn:mfin.gov.hr:cius-2025:1.0`

---

Stojim na raspolaganju za sva dodatna pitanja i testiranja.

S poštovanjem,  
Marijan Kopčić  
MD SOLUTIONS VL. MARINA KOPČIĆ  
K. F. ŠEFERA 29, 31431 ČEPIN  
OIB: 86058362621  
📧 mkopcic@gmail.com  
📱 +385 97 7791402

---

## Što priložiti uz mail

- [ ] Ispunjena **Pristupnica za internetski servis Fina e-Račun** s rukom napisanim "Demo" — preuzeti s https://www.fina.hr/ngsite/content/download/9166/164444/1
- [ ] Preslika osobne iskaznice skrbnika (KOPČIĆ MARIJAN)
- [ ] Opcionalno: [FINA_REQUEST_certificate.txt](../e-racun/FINA_REQUEST_certificate.txt) — detalji o certifikatu

---

## Napomene

- Stari mail iz **veljače 2026.** (`docs/e-racun/FINA_REQUEST_EMAIL_TEMPLATE.txt`) bio je pogrešno fokusiran — tražio je endpoint bez da je ikad poslana pristupnica. Bez pristupnice FINA ne može aktivirati OIB.
- Pristupnica je **obavezna** čak i za demo okolinu.
- Nakon što FINA aktivira OIB i pošalje endpoint URL, jedina promjena u kodu je `ERACUN_DEMO_URL` u `.env` — sve ostalo je implementirano i spremno.
