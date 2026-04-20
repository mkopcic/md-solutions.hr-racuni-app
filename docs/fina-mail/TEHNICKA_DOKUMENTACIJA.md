# Tehnička dokumentacija - e-Račun B2B integracija

**Datum:** 20. travnja 2026.  
**Subjekt:** MD SOLUTIONS VL. MARINA KOPČIĆ  
**OIB:** 86058362621  
**Kontakt:** mkopcic@gmail.com

---

## 1. Infrastruktura aplikacije

| Parametar | Vrijednost |
|---|---|
| Platforma | Laravel 12 / PHP 8.4.20 |
| Server | Linux (Hetzner VPS) |
| Domena | md-solutions.hr |
| SOAP klijent | Guzzle HTTP (direktni XML POST) |
| XML potpisivanje | `robrichards/xmlseclibs` (RSA-SHA256) |
| UBL format | UBL 2.1, CIUS-HR-2025 |

---

## 2. Certifikat

| Parametar | Vrijednost |
|---|---|
| Subject CN | MD SOLUTIONS RAČUNI |
| Subject O | MD SOLUTIONS VL. MARINA KOPČIĆ |
| Issuer CN | Fina Demo CA 2020 |
| Serijski broj | F9FDF14633C09044779B4E2FA9AD7294 |
| SHA1 Fingerprint | `3E:C2:ED:05:A0:25:4C:6E:33:4B:2B:BE:47:E1:C5:D6:88:68:B8:DC` |
| Validan od | 2026-02-26 22:27:43 UTC |
| Validan do | 2030-07-31 14:30:18 UTC |
| Key Usage | Digital Signature, Key Encipherment |
| Extended Key Usage | E-mail Protection, TLS Web Client Authentication |
| Subject Alt Name | email:mkopcic@gmail.com |
| Format na serveru | PEM (ekstraktiran iz originalnog PKCS#12) |
| Lokacija na serveru | `/storage/certificates/86058362621.A.4.pem` |

Certifikat je uspješno učitan, private key je validan i par je provjeren (MD5 moduli se poklapaju).

---

## 3. Što je implementirano

### 3.1 UBL 2.1 generator

Implementiran je `UblInvoiceGenerator` koji generira UBL 2.1 XML prema specifikaciji:

```
CustomizationID: urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0
                 #conformant#urn:mfin.gov.hr:ext-2025:1.0
ProfileID:       urn:fdc:peppol.eu:2017:poacc:billing:01:1.0
```

Generirani dokument sadrži:
- `AccountingSupplierParty` s OIB-om i adresom dobavljača
- `AccountingCustomerParty` s OIB-om kupca (schemeID `9934`)
- `PaymentMeans` (PaymentMeansCode 10/30/48)
- `TaxTotal` s razradom po PDV stopama
- `LegalMonetaryTotal`
- `InvoiceLine` stavke s `ClassifiedTaxCategory`

### 3.2 XMLDSig potpis UBL dokumenta

UBL dokument se potpisuje koristeći `XmlSigner::sign()`:
- Algoritam: RSA-SHA256
- Kanonizacija: Exclusive C14N
- Certifikat se prilaže u `KeyInfo/X509Data`

### 3.3 WS-Security potpis SOAP headera

SOAP envelope se potpisuje koristeći `XmlSigner::signSoapEnvelope()`:
- `wsse:BinarySecurityToken` (X509v3, Base64 DER)
- `ds:Signature` u `wsse:Security` headeru
- Potpisuje se SOAP Body (referenca `#Body`)
- `wsse:SecurityTokenReference` referencira `#X509Token`

### 3.4 HTTPS klijent autentifikacija

HTTP zahtjev se šalje s client certifikatom:
- TLS handshake s FINA serverima: **uspješan**
- Client certifikat se prilaže uz svaki zahtjev

---

## 4. SOAP poruka - struktura zahtjeva

Namespace koji koristimo za e-Račun:
```
xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73"
```

Struktura `SendB2BOutgoingInvoiceRequest`:

```xml
<b2b:HeaderSupplier>
    <b2b:MessageID>{unique_id}</b2b:MessageID>
    <b2b:SupplierID>9934:86058362621</b2b:SupplierID>
    <b2b:ERPID>LARAVEL_RACUNI_APP</b2b:ERPID>
    <b2b:MessageType>9001</b2b:MessageType>
</b2b:HeaderSupplier>
```

Puni primjer SOAP zahtjeva u prilogu: **SOAP_REQUEST_PRIMJER.xml**

---

## 5. Što radi, što ne radi

### ✅ Funkcionalno (potvrđeno)

- Učitavanje certifikata iz PEM formata
- Private key ekstrakcija i validacija
- UBL 2.1 XML generiranje
- XMLDSig potpis UBL dokumenta (RSA-SHA256)
- WS-Security potpis SOAP headera
- HTTPS konekcija s client certifikatom prema FINA serverima
- TLS handshake (provjeren na `cistest.apis-it.hr:8449`)

### ❌ Nije moguće testirati

- Slanje računa — nedostaje demo endpoint URL
- Provjera statusa računa — nedostaje demo endpoint URL
- Dohvat ulaznih računa — nedostaje demo endpoint URL

---

## 6. Testirani endpointi (neuspješno)

| Endpoint | DNS | TCP/TLS | Rezultat |
|---|---|---|---|
| `demo-eracun.fina.hr` | ❌ NXDOMAIN | — | Domain ne postoji |
| `ws-test.fina.hr` | ❌ NXDOMAIN | — | Domain ne postoji |
| `eracun.fina.hr` | ✅ 89.249.110.130 | ❌ Timeout | TCP connection timeout |
| `cistest.apis-it.hr:8449/EracunServiceTest` | ✅ | ✅ | HTTP 500 — fiskalizacijski odgovor (s006) |

**Napomena:** `cistest.apis-it.hr:8449` je fiskalizacijski server — odgovara s XML-om koji referencira `FiskalizacijaSchema.xsd` i potpisan je od `CN=fiskalcistest`. Ne prepoznaje e-Račun zahtjeve.

---

## 7. Potrebno od FINA-e

1. **Točan URL demo endpointa** za e-Račun B2B (SOAP)
2. **Aktivacija OIB-a 86058362621** u demo sustavu
3. Potvrda je li namespace `http://www.apis-it.hr/fin/2012/types/f73` ispravan za e-Račun ili se koristi drugi
4. Potvrda je li `MessageType: 9001` (SendB2BOutgoingInvoice) ispravan za sinkroni model

---

*Dokument pripremljen: 20. travnja 2026.*
