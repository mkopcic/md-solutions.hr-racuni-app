# FINA e-Račun B2B - Kako ispravno implementirati

**Datum:** 18. travnja 2026.  
**Temelji se na:** FINA tehničkoj specifikaciji (sinkrona obrada), analizi koda i logova  
**OIB:** 86058362621 | **Certifikat:** `86058362621.A.4` (validan do 31.07.2030.)

---

## Pregled: Što imate, što nedostaje

| Komponenta | Status | Komentar |
|-----------|--------|---------|
| Demo certifikat | ✅ Ispravan | Fina Demo CA 2020, PEM format radi |
| OIB format u SOAP-u | ✅ Ispravan | `9934:86058362621` je točno |
| UBL 2.1 XML generiranje | ✅ Radi | Struktura ispravna |
| XMLDSig potpis UBL-a | ✅ Radi | `EracunService` poziva `xmlSigner->sign()` prije slanja |
| TLS client certificate | ✅ Radi | Potvrđeno na fiskalizacija endpointu |
| **Registracija OIB-a** | ❌ Nedostaje | Bez toga ništa ne radi |
| **WS-Security na SOAP headeru** | ❌ Nedostaje | SOAP header je prazan - vidi razliku vs fiskalizacija |
| **Endpoint URL** | ❌ Pogrešan | Šalje na fiskalizacijski server (s006!) |
| **XML deklaracija u SOAP body** | ⚠️ Potencijalni problem | Može uzrokovati s006 - vidi točku 7 |
| **CA certifikat za TLS verify** | ⚠️ Zaobilažen | `verify => false` - nesigurno za produkciju |

---

## 1. Registracija - Bez toga nema ništa

Ovo je nulti korak bez kojeg nijedna tehnika ne radi. Vaš OIB **nije upisan** u FINA-in e-Račun demo sustav. Upravo zato `digitalneusluge.fina.hr/eRacunB2B` vraća **403**.

### Što poslati na `eracun.itpodrska@fina.hr`

**Obavezno:**
- Ispunjena [Pristupnica za internetski servis Fina e-Račun](https://www.fina.hr/ngsite/content/download/9166/164444/1) - ručno dodajte "**Demo**" na pristupnici
- Preslika osobne iskaznice skrbnika (Marijan Kopčić)

**Opcionalno (preporučeno za lakši put):**
- [Zahtjev za integracijski modul](https://www.fina.hr/ngsite/content/download/12653/189705/1) - besplatni modul koji sam rješava SOAP i potpis

---

## 2. OIB format - Jeste li ga točno slali?

**DA, format je ispravan.** Dokumentacija kaže: `SupplierID = 9934:12345678909`

Vaš kod šalje: `9934:86058362621` ✅

Ovo je ICD (International Code Designator) format:
- `9934` = oznaka za Hrvatski OIB sustav
- Iza dvotočke ide OIB bez prefiksa `HR`

---

## 3. Pogrešan endpoint - Koji koristiti

### Trenutno (POGREŠNO):
```
https://cistest.apis-it.hr:8449/EracunServiceTest?wsdl
```
Ovaj server je **FISKALIZACIJA** server (APIS IT). Vraća `s006` jer ne zna ništa o e-Računima.

### Ispravno - Sinkrona specifikacija (novi, lakši način):

FINA ima **dva pristupa** - koristite sinkroni, ne asinkroni:

| | Asinkroni (stari) | **Sinkroni (novi - koristite ovo)** |
|-|-------------------|--------------------------------------|
| Vaš web servis | Morate imati | Ne trebate |
| Odgovor | FINA vam šalje asinkrono | FINA odgovara odmah |
| Složenost | Visoka | Niska |
| Trenutna implementacija | Da (krivo) | Ne (trebate ovo) |

**Demo endpoint (sinkroni):**
```
https://eracun-demo.fina.hr/axis2/services/SendB2BOutgoingInvoice
```
> NAPOMENA: Točan demo URL dobivate od FINA-e nakon registracije. Gore navedeni je pretpostavljeni - ne postoji javni popis.

**Produkcijski endpoint:**
```
https://eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice
```

**WSDL za sinkronu specifikaciju:**  
https://www.fina.hr/ngsite/content/download/13358/204205/1

### Metode sinkronog web servisa:
```
EchoMsg                        → testiranje konekcije
SendB2BOutgoingInvoiceMsg      → slanje računa (tip poruke: 9001)
GetB2BOutgoingInvoiceStatusMsg → provjera statusa (tip poruke: 9011)
```

---

## 4. WS-Security - Razlika između Fiskalizacije i e-Računa

> **FINA spec:** "Zaštita integriteta podataka u SOAP poruci ostvaruje se digitalnim potpisivanjem SOAP poruke digitalnim certifikatom korištenjem **WS-Security standarda**."

### Fiskalizacija (radi) vs e-Račun (ne radi) - ključna razlika

**Fiskalizacija** - potpis je **unutar Body elementa** (enveloped XMLDSig):
```xml
<soapenv:Envelope>
   <soapenv:Header/>                    ← prazan, FINA prihvaća
   <soapenv:Body>
      <RacunZahtjev Id="RacunZahtjev-uuid">
         ...
         <ds:Signature>                 ← UNUTAR body elementa
            <ds:Reference URI="#RacunZahtjev-uuid"/>
         </ds:Signature>
      </RacunZahtjev>
   </soapenv:Body>
</soapenv:Envelope>
```

**e-Račun** - potpis mora biti **u SOAP headeru** (WS-Security standard):
```xml
<soapenv:Envelope xmlns:wsse="...wss-wssecurity-secext-1.0.xsd"
                  xmlns:wsu="...wss-wssecurity-utility-1.0.xsd">
   <soapenv:Header>
      <wsse:Security mustUnderstand="1">
         <wsse:BinarySecurityToken wsu:Id="X509Token"
            ValueType="...#X509v3"
            EncodingType="...#Base64Binary">
            <!-- Base64 DER certifikat -->
         </wsse:BinarySecurityToken>
         <ds:Signature>
            <ds:Reference URI="#Body"/>  ← referencira Body element
         </ds:Signature>
      </wsse:Security>
   </soapenv:Header>
   <soapenv:Body wsu:Id="Body">         ← Body dobiva Id za referencu
      ...
   </soapenv:Body>
</soapenv:Envelope>
```

### Što se može reuse-ati iz Fiskalizacije

Ista `robrichards/xmlseclibs` biblioteka - samo drugačija struktura:
- Fiskalizacija: `appendSignature($racunZahtjevElement)` → potpis u body elementu
- e-Račun: `appendSignature($headerSecurityElement)` + `addReference($bodyElement)` → WS-Security

Šifra grešaka pri lošem potpisu: FINA vraća `AckStatusCode = 91` ("Potpis poruke nije ispravan").

---

## 5. Dvije odvojene razine potpisa za e-Račun

Za e-Račun su **potrebna dva odvojena XMLDSig potpisa** (za razliku od fiskalizacije koja ima jedan):

| | Razina | Algoritam | Gdje |
|-|--------|-----------|------|
| 1. | UBL XML dokument | RSA-SHA256 | Embeded unutar UBL XML-a (enveloped) |
| 2. | SOAP envelope | RSA-SHA256 | WS-Security header |

**Dobra vijest:** `EracunService.php` već pravilno potpisuje UBL ✅:
```php
$ublXml    = $this->ublGenerator->generate($invoice);
$signedUbl = $this->xmlSigner->sign($ublXml);      // ← UBL se potpisuje
$response  = $this->client->sendInvoice($invoice, $signedUbl);  // šalje potpisani
```

**Što nedostaje:** SOAP envelope WS-Security (u `FinaEracunClient::buildSendInvoiceRequest()`).

---

## 6. CA certifikat - Što to znači i treba li ga?

> Korisnikovo pitanje: "jel potrebno možda njihov CA preuzeti pa tako podpisati?"

**Kratki odgovor: Ne potpisujete s njihovim CA. On služi samo za TLS.**

Postoje dva smjera u komunikaciji:

```
Vaša app ──[vaš cert]──→ FINA server   ← Client auth (potpisujete VI, vašim privatnim ključem)
Vaša app ←──[FINA cert]── FINA server  ← Server auth (verify = FINA CA, provjerava vaša app)
```

| Stvar | Čemu služi | Koji certifikat |
|-------|-----------|-----------------|
| Potpisivanje SOAP-a / UBL-a | Integritet podataka, autentičnost | **VAŠ** private key + vaš cert |
| TLS client auth | FINA zna tko ste | **VAŠ** cert (Fina Demo CA 2020 potpisao) |
| TLS server verify | Vi znate da pričate s FINA-om | **FINA-in root CA** cert |

### Zašto `verify => false` nije problem za demo, ali jest za produkciju

FINA Demo CA 2020 cert postoji u `storage/certificates/fina-demo-ca-root.pem`:
```php
// Trenutno (nesigurno):
'verify' => false

// Ispravno:
'verify' => storage_path('certificates/fina-demo-ca-root.pem')
```

FINA već prihvaća vaš certifikat jer ga je sama izdala - to ne mijenja `verify` postavka. `verify` samo znači da vi provjeravate njihov server.

### Zašto je FINA demo endpoint možda i prihvaćala bez WS-Security?

Moguće je da demo okolina ima opuštenije provjere od produkcije. To je razlog zašto testiranje na demu ne garantira da će produkcija raditi.

---

## 7. s006 greška - Ista greška kao u Fiskalizaciji, isti uzrok?

`fiskal_fix.md` dokumentira da je fiskalizacija **identičnu s006 grešku** dobila zbog:
> "SOAP body was malformed (nested XML declaration)"

**Fix u fiskalizaciji:** Ukloniti `<?xml version="1.0"?>` deklaraciju iz XML-a **prije** umetanja u SOAP envelope.

**Provjera u e-Račun kodu:** `UblInvoiceGenerator::generate()` vraća XML s deklaracijom. `XmlSigner::sign()` -> `$doc->saveXML()` vraća XML **s deklaracijom**. Ta vrijednost (`$signedUbl`) se direktno `base64_encode()`-a i stavlja u SOAP. Ovo je ispravno jer je UBL enkodiran u Base64 omotnici.

**Pravi uzrok s006 ovdje:** Pogrešan endpoint. SOAP ide na fiskalizacijski server koji ne zna što je `SendB2BOutgoingInvoiceRequest`. Ako se poveže na pravi e-Račun endpoint, s006 bi trebao nestati (osim ako nema WS-Security koji odbacuje kao 90/91).

---

## 8. Veza s Fiskalizacijom 2.0

Vaša fiskalizacija implementacija ima svu tehničku osnovu - **samo drugačiji oblik potpisa:**

| Komponenta | Fiskalizacija | e-Račun |
|-----------|--------------|---------|
| Certifikat tip | FINA PKI | FINA PKI (isti!) |
| TLS mutual auth | Da | Da |
| XMLDSig biblioteka | `robrichards/xmlseclibs` | Isti |
| Algoritam potpisa | RSA-SHA1 (fiskal spec) | RSA-SHA256 (e-Račun spec) |
| Namespace | `fin/2012/types/f73` | `fin/2012/types/f73` (isti!) |
| **Gdje ide potpis** | **Unutar body elementa** | **WS-Security header** |
| Broj potpisa | 1 (na body elementu) | 2 (UBL + SOAP WS-Security) |

`robrichards/xmlseclibs` korišten u fiskalizaciji **direktno podržava WS-Security** - samo drugačija metoda poziva.

---

## 9. Ispravna SOAP struktura za EchoMsg (s WS-Security)

Prema sinkronoj specifikaciji. Namespace `b2b` je isti kao i u fiskalizaciji:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
   xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
   xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73"
   xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
   xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"
   xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header>
      <wsse:Security soapenv:mustUnderstand="1">
         <wsse:BinarySecurityToken
            wsu:Id="X509Token"
            ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"
            EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
            BASE64_ENCODED_DER_CERT
         </wsse:BinarySecurityToken>
         <ds:Signature>
            <ds:SignedInfo>
               <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
               <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
               <ds:Reference URI="#Body">
                  <ds:Transforms>
                     <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                  </ds:Transforms>
                  <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                  <ds:DigestValue>...</ds:DigestValue>
               </ds:Reference>
            </ds:SignedInfo>
            <ds:SignatureValue>...</ds:SignatureValue>
            <ds:KeyInfo>
               <wsse:SecurityTokenReference>
                  <wsse:Reference URI="#X509Token"/>
               </wsse:SecurityTokenReference>
            </ds:KeyInfo>
         </ds:Signature>
      </wsse:Security>
   </soapenv:Header>
   <soapenv:Body wsu:Id="Body">
      <b2b:EchoRequest>
         <b2b:HeaderSupplier>
            <b2b:MessageID>ECHO_unique_id_123</b2b:MessageID>
            <b2b:SupplierID>9934:86058362621</b2b:SupplierID>
            <b2b:ERPID>RACUNI_APP</b2b:ERPID>
            <b2b:MessageType>9999</b2b:MessageType>
         </b2b:HeaderSupplier>
         <b2b:Data>
            <b2b:EchoData>
               <b2b:Echo>Test poruka</b2b:Echo>
            </b2b:EchoData>
         </b2b:Data>
      </b2b:EchoRequest>
   </soapenv:Body>
</soapenv:Envelope>
```

**Napomene:**
- `AdditionalSupplierID` s `HR99:00001` je hard-coded i pogrešan → makni ga (opcionalno polje, treba biti vaša registrirana poslovna jedinica)
- `wsu:Id="Body"` na Body elementu je obavezan (Signature Reference URI="#Body")
- Certifikat u `BinarySecurityToken` = Base64 DER format (ne PEM)

---

## 10. SpecificationIdentifier - Provjera

Za slanje računa između hrvatskih poreznih obveznika koristite:
```
urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0
```

Vaš kod već koristi ovu vrijednost ✅

---

## 11. Redoslijed implementacije (akcijski plan)

### Faza 1 - Administrativno (blocker - bez toga ne možete testirati)
1. Poslati **Pristupnicu** (s "Demo") + kopiju osobne na `eracun.itpodrska@fina.hr`
2. Čekati aktivaciju OIB-a - FINA šalje potvrdu i demo endpoint URL

### Faza 2 - Tehnički (dok čekate registraciju možete pripremiti)

**Opcija A - FINA integracijski modul (besplatno, lakše):**
- Zatražiti modul u istom mailu s Pristupnicom
- Modul prima REST/JSON, interno šalje SOAP s WS-Security - ne morate sami implementirati
- Izmijeniti `FinaEracunClient.php` za REST pozive prema lokalnom modulu

**Opcija B - Direktna SOAP implementacija (imate sve osim WS-Security):**
1. Implementirati `buildWsSecurity()` metodu u `FinaEracunClient.php`:
   - Uzeti certifikat iz `CertificateLoader` (DER format = base64 der bez PEM headera)
   - Dodati `wsu:Id="Body"` na SOAP Body element
   - Potpisati Body element pomoću `robrichards/xmlseclibs`
   - Umetnuti `<wsse:Security>` u header
2. Zamijeniti endpoint s URL-om koji FINA da
3. Maknuti hard-coded `HR99:00001` iz `AdditionalSupplierID`
4. Eventualno zamijeniti `'verify' => false` s `fina-demo-ca-root.pem`
5. Testirati EchoMsg → zatim `SendB2BOutgoingInvoiceMsg`

---

## 12. Greške koje FINA vraća i što znače

| Šifra | Značenje | Uzrok u vašem slučaju |
|-------|---------|----------------------|
| `s006` | Sistemska greška | **Pogrešan endpoint** (fiskalizacijski server, ne e-Račun) |
| `90` | XML poruka nije ispravna | WS-Security nedostaje ili pogrešna struktura |
| `91` | Potpis poruke nije ispravan | WS-Security potpis neispravan (probajte canonical C14N) |
| `151` | Nepoznat ID subjekta | **OIB nije registriran** (Pristupnica!) |
| `152` | Nema prava slanja | Certifikat nije vezan uz registrirani OIB |

**Fiskalizacijska paralela (iz `fiskal_fix.md`):**
- `s006` → malformed SOAP ili pogrešan endpoint
- `s004` → neispravan potpis (kriva canonicalizacija)
- `s005` → OIB u XML-u ≠ OIB na certifikatu

---

## Sažetak u jednoj rečenici

Imate sve tehničke komponente iz fiskalizacije (certifikat, `robrichards/xmlseclibs`, TLS) - UBL se već potpisuje ispravno, jedino što nedostaje je **WS-Security potpis na SOAP headeru** (struktura malo drugačija od fiskalizacijskog body-potpisa), ali ni to ne možete testirati dok FINA ne aktivira vaš OIB putem Pristupnice.

---

*Dokument kreiran: 18. travnja 2026. | Temelji se na: FINA tehničkoj spec (sinkrona), analizi koda (`EracunService.php`, `FinaEracunClient.php`, `XmlSigner.php`), `docs/fiskalizacija/fiskal_fix.md`, `docs/fiskalizacija/ispravna_poruka.txt`*
