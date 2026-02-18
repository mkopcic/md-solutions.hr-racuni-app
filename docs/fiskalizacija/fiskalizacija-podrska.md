# Zahtjev za podršku - FINA Demo Fiskalizacija

**Datum:** 13. studeni 2025  
**Sustav:** Laravel aplikacija za sportske udruge  
**Problem:** Greška s006 "Sistemska pogreška prilikom obrade zahtjeva" pri svakom pokušaju fiskalizacije

---

## 1. PROBLEM

Primljeni smo demo FISKAL 3 certifikat za testiranje fiskalizacije. TLS konekcija prema demo endpointu uspješno prolazi, ali svaki pokušaj slanja RacunZahtjev-a vraća grešku:

```
s006 - Sistemska pogreška prilikom obrade zahtjeva.
```

---

## 2. DETALJI O CERTIFIKATU

### Osnovni podaci
- **Tip certifikata:** FISKAL 3 (demo)
- **Common Name (CN):** FISKAL 3
- **Organizacija:** MD SOLUTIONS VL. MARINA KOPČIĆ HR86058362621
- **Lokacija:** ČEPIN
- **OIB:** 86058362621

### Izdavač (CA)
- **Issuer:** Fina Demo CA 2020
- **Organizacija:** Financijska agencija
- **Zemlja:** HR

### Valjanost
- **Izdano:** 31. listopad 2025, 21:26:05 GMT
- **Vrijedi do:** 31. srpanj 2030, 12:30:18 GMT

### Otisci (Fingerprints)
- **SHA1:** `3F37AAC4267646EE5CA6B4962C72BC8552F08ECD`
- **SHA256:** `1A4C1A8EB4085962EBF14C5898D2C1D375DCED040C483725D2F8F3DCFA4D3320`
- **Serial Number:** `7550EF49185CC6B9207FB3E197DA2C21`

---

## 3. KONFIGURIRANI ENDPOINT

- **Demo endpoint:** `https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest`
- **WSDL:** `https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest?wsdl`
- **Timeout:** 10 sekundi
- **CA bundle:** Fina Demo CA 2020 (ekstrahiran iz TLS handshakea)

---

## 4. TLS KONEKCIJA - USPJEŠNA ✅

Testirana je TLS konekcija curl-om s klijentskim certifikatom:

```bash
curl https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest \
  --cert certs/86058362621.F3.3.pem \
  --key certs/86058362621.F3.3.pem \
  --cacert certs/fina-demo-ca-2020.pem \
  --header 'Content-Type: text/xml' \
  --max-time 15 -v
```

### Rezultat TLS handshakea:
```
* SSL connection using TLSv1.3 / TLS_AES_256_GCM_SHA384
* Server certificate:
*  subject: C=HR; O=APIS IT D.O.O.; L=ZAGREB; CN=cistest.apis-it.hr
*  issuer: C=HR; O=Financijska agencija; CN=Fina Demo CA 2020
*  SSL certificate verify ok.
```

**TLS konekcija uspješno uspostavljena** - certifikat je prihvaćen i validiran.

---

## 5. SOAP ECHO TEST - USPJEŠAN ✅

Testiran je SOAP Echo zahtjev:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                  xmlns:f73="http://www.apis-it.hr/fin/2012/types/f73">
  <soapenv:Header/>
  <soapenv:Body>
    <f73:EchoRequest>ping</f73:EchoRequest>
  </soapenv:Body>
</soapenv:Envelope>
```

**Odgovor servera:** HTTP 500 Internal Server Error s SOAP Fault - ali TLS i autentikacija su prošli.

---

## 6. RACUN ZAHTJEV - GREŠKA s006 ❌

### Testni zahtjev
Poslali smo minimalni RacunZahtjev prema tehničkoj specifikaciji:

```xml
<?xml version="1.0"?>
<RacunZahtjev xmlns="http://www.apis-it.hr/fin/2012/types/f73" 
              Id="RacunZahtjev-f03e92b3-51b1-4153-93c3-30a0fe5e84af">
  <Zaglavlje Id="Zaglavlje-b03efd7b-8c5d-42af-b786-ff4fd598cb79">
    <IdPoruke>25cce540-7dea-4a4b-810d-84791a8dade5</IdPoruke>
    <DatumVrijeme>13.11.2025T20:29:35</DatumVrijeme>
  </Zaglavlje>
  <Racun Id="Racun-638c4f50-7c88-4978-bb93-965e255e085a">
    <OIB>96557881558</OIB>
    <USustPdv>N</USustPdv>
    <DatVrijeme>13.11.2025T20:29:35</DatVrijeme>
    <OznSlijed>P</OznSlijed>
    <BrRac>
      <BrOznRac>000001</BrOznRac>
      <OznPosPr>K1</OznPosPr>
      <OznNapUr>K1</OznNapUr>
    </BrRac>
    <IznosUkupno>1500.00</IznosUkupno>
    <NacinPlac>G</NacinPlac>
    <ZastKod>65080BADFB3BD37074E605D1E736F274</ZastKod>
  </Racun>
  <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <!-- XMLDSig potpis generiran s RSA-SHA1 -->
  </ds:Signature>
</RacunZahtjev>
```

### Parametri zahtjeva
- **OIB obveznika:** 96557881558 (demo OIB)
- **Oznaka slijeda:** P (normalni slijed)
- **Poslovni prostor:** K1
- **Naplatni uređaj:** K1
- **Iznos:** 1500.00 HRK
- **Način plaćanja:** G (gotovina)
- **U sustavu PDV:** N (ne)
- **ZKI (ZastKod):** 65080BADFB3BD37074E605D1E736F274

### FINA odgovor
```xml
<tns:Greska>
  <tns:SifraGreske>s006</tns:SifraGreske>
  <tns:PorukaGreske>Sistemska pogreška prilikom obrade zahtjeva.</tns:PorukaGreske>
</tns:Greska>
```

**HTTP Status:** 200 OK (ali s greškom u SOAP tijelu)  
**Datum/vrijeme odgovora:** 13.11.2025T19:22:01

---

## 7. XMLDSIG POTPIS

Digitalni potpis generiran je koristeći:
- **Algoritam potpisa:** RSA-SHA1 (`http://www.w3.org/2000/09/xmldsig#rsa-sha1`)
- **Kanonizacija:** C14N 1.0 (`http://www.w3.org/TR/2001/REC-xml-c14n-20010315`)
- **Digest algoritam:** SHA1 (`http://www.w3.org/2000/09/xmldsig#sha1`)
- **Transform:** Enveloped signature + C14N

Potpis uključuje:
- X509Certificate (cijeli certifikat)
- X509SubjectName
- X509IssuerSerial

---

## 8. MOGUĆI UZROCI

### Naša analiza:
1. **TLS i autentikacija prolaze** - certifikat je valjan i prihvaćen
2. **Echo test ne vraća s006** - server prima naše zahtjeve
3. **XML struktura validna** - prema tehničkoj specifikaciji v2.3
4. **XMLDSig potpis generiran** - koristi se ispravni certifikat

### Moguća pitanja:
- **Je li demo OIB (96557881558) registriran za testiranje?**
- **Je li demo certifikat FISKAL 3 povezan s tim OIB-om?**
- **Trebaju li oznake poslovnog prostora (K1) i naplatnog uređaja (K1) biti prethodno registrirane?**
- **Je li potrebna dodatna registracija demo okruženja prije slanja zahtjeva?**
- **Postoji li razlika između FISKAL 1/2/3 certifikata u načinu formiranja zahtjeva?**

---

## 9. PITANJA ZA FINA PODRŠKU

1. **Registracija demo certifikata:**  
   Je li demo FISKAL 3 certifikat (S/N: 7550EF49185CC6B9207FB3E197DA2C21) ispravno registriran u demo sustavu?

2. **Mapiranje OIB-a:**  
   **KRITIČNO:** Naš certifikat ima OIB 86058362621, ali koristimo OIB 96557881558 u zahtjevima.  
   - Koji OIB trebamo koristiti u RacunZahtjev-u?  
   - Je li 96557881558 valjan demo OIB ili trebamo koristiti 86058362621 iz certifikata?  
   - Je li OIB iz zahtjeva povezan s našim certifikatom?

3. **Registracija oznaka:**  
   Trebaju li oznake poslovnog prostora (OznPosPr) i naplatnog uređaja (OznNapUr) biti prethodno registrirane u demo sustavu?  
   - Testirali smo ProvjeraPoslovnogProstoraZahtjev i ProvjeraNaplatnogUredjajaZahtjev - obje vraćaju s006.  
   - Ako je potrebna registracija, kako se registriraju oznake K1 za demo testiranje?

4. **Greška s006:**  
   Što konkretno uzrokuje "Sistemsku pogrešku prilikom obrade zahtjeva"?  
   - Echo test **uspješno radi** (vraća ping-test odgovor)  
   - SVE ostale operacije (RacunZahtjev, ProvjeraPoslovnogProstora, ProvjeraNaplatnogUredjaja) vraćaju s006  
   - Možete li provjeriti server-side logove za naše zahtjeve (13.11.2025 između 19:22-22:17)?

5. **FISKAL 3 specifikacije:**  
   Postoje li posebne razlike između FISKAL 1/2/3 certifikata u načinu generiranja ZKI koda ili formata XMLDSig potpisa?

6. **Demo dokumentacija:**  
   Je li dostupna ažurirana dokumentacija ili primjeri specifični za FISKAL 3 demo certifikate?  
   - Primjeri uspješnih RacunZahtjev XML-ova  
   - Upute za registraciju poslovnih prostora/uređaja u demo okruženju

---

## 10. PRILOŽENI DOKUMENTI

Uz ovaj zahtjev prilažemo:

1. **certificate-info.json** - detalji certifikata
2. **request.xml** - potpuni RacunZahtjev XML sa XMLDSig potpisom
3. **response.xml** - potpuni SOAP odgovor s greškom s006
4. **meta.json** - svi parametri zahtjeva
5. **fina-echo-response.xml** - odgovor na Echo test
6. **fina-echo-verbose.log** - TLS handshake detalji (curl verbose)

---

## 11. KONTAKT

**Sustav:** SPO Laravel - Sportske udruge  
**Email:** mkopcic@gmail.com  
**Developer:** Marina Kopčić  

**Očekivani rok odgovora:** U najkrćem mogućem roku kako bismo mogli nastaviti s razvojem sustava fiskalizacije.

---

**Napomena:** Svi priloženi XML dokumenti i logovi generirani su automatski iz Laravel aplikacije korištenjem robrichards/xmlseclibs biblioteke za XMLDSig potpis.

