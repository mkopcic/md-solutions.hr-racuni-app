# e-Račun: WS-Security implementacija

**Što treba napraviti:** Dodati metodu `signSoapEnvelope()` u `XmlSigner.php` i pozvati je iz `FinaEracunClient.php` na svim `buildXxxRequest()` metodama.

Sve ostalo (certifikat, UBL potpis, `CertificateLoader`, TLS) već radi.

---

## 1. Nova metoda u `XmlSigner.php`

Dodati **jednu** novu `public` metodu ispod postojeće `sign()`:

```php
/**
 * Potpisuje SOAP envelope s WS-Security standardom.
 * Dodaje <wsse:Security> element u SOAP Header i potpisuje Body.
 *
 * Za razliku od sign() koji potpisuje UBL dokument (enveloped),
 * ova metoda stavlja potpis u SOAP Header (WS-Security).
 */
public function signSoapEnvelope(string $soapXml): string
{
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->loadXML($soapXml);

    $nsEnv  = 'http://schemas.xmlsoap.org/soap/envelope/';
    $nsWsse = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    $nsWsu  = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    $nsX509 = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3';
    $nsEnc  = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('soapenv', $nsEnv);

    // 1. Pronađi Header i Body elemente
    $header = $xpath->query('/soapenv:Envelope/soapenv:Header')->item(0);
    $body   = $xpath->query('/soapenv:Envelope/soapenv:Body')->item(0);

    if ($header === null || $body === null) {
        throw new Exception('Neispravan SOAP envelope - nedostaje Header ili Body.');
    }

    // 2. Dodaj wsu:Id na Body element (potrebno za referencu u potpisu)
    $body->setAttributeNS($nsWsu, 'wsu:Id', 'Body');

    // 3. Pripremi certifikat u DER/Base64 formatu za BinarySecurityToken
    $certPem = $this->certLoader->getCertificate();
    $certDer = base64_encode(
        base64_decode(
            preg_replace('/-----[^-]+-----|[\r\n\s]/', '', $certPem)
        )
    );

    // 4. Kreiraj <wsse:Security> element u Headeru
    $security = $doc->createElementNS($nsWsse, 'wsse:Security');
    $security->setAttributeNS($nsEnv, 'soapenv:mustUnderstand', '1');

    // 5. Dodaj <wsse:BinarySecurityToken>
    $bst = $doc->createElementNS($nsWsse, 'wsse:BinarySecurityToken', $certDer);
    $bst->setAttributeNS($nsWsu, 'wsu:Id', 'X509Token');
    $bst->setAttribute('ValueType', $nsX509);
    $bst->setAttribute('EncodingType', $nsEnc);
    $security->appendChild($bst);

    $header->appendChild($security);

    // 6. Potpiši Body element koristeći xmlseclibs
    $objDSig = new XMLSecurityDSig;
    $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

    // Referenciraj Body element (ne cijeli dokument)
    $objDSig->addReference(
        $body,
        XMLSecurityDSig::SHA256,
        [XMLSecurityDSig::EXC_C14N],
        ['overwrite' => false]
    );

    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
    $objKey->loadKey($this->certLoader->getPrivateKey());

    $objDSig->sign($objKey);

    // 7. KeyInfo - SecurityTokenReference na BinarySecurityToken
    $keyInfo = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:KeyInfo');
    $strElem = $doc->createElementNS($nsWsse, 'wsse:SecurityTokenReference');
    $refElem = $doc->createElementNS($nsWsse, 'wsse:Reference');
    $refElem->setAttribute('URI', '#X509Token');
    $refElem->setAttribute('ValueType', $nsX509);
    $strElem->appendChild($refElem);
    $keyInfo->appendChild($strElem);

    // Dodaj KeyInfo u Signature element
    $objDSig->sigNode->appendChild($keyInfo);

    // 8. Dodaj potpis u Security header (ne u Body!)
    $objDSig->appendSignature($security);

    return $doc->saveXML();
}
```

---

## 2. Izmjene u `FinaEracunClient.php`

### 2a. Metoda `sendSoapViaHttp()` - dodati poziv potpisivanja

Umjesto da svaka `buildXxxRequest()` vraća SOAP i odmah šalje, treba potpisati SOAP **prije** slanja. Najlakše u `sendSoapRequest()`:

```php
protected function sendSoapRequest(string $method, string $xmlEnvelope): string
{
    try {
        // ← DODATI OVO: potpiši SOAP envelope prije slanja
        $xmlEnvelope = $this->xmlSigner->signSoapEnvelope($xmlEnvelope);

        $response = $this->sendSoapViaHttp($xmlEnvelope);
        // ... ostatak ostaje isti
    }
}
```

Ovo je **jedna linija** promjene - sve build metode automatski dobivaju WS-Security.

### 2b. Izmjena `buildEchoRequest()` - dodati namespace-ove

Envelope mora imati `wsse` i `wsu` namespace-ove jer `signSoapEnvelope()` upisuje elemente u te namespace-ove:

```php
protected function buildEchoRequest(string $messageId, string $message): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73"
    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
    xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header/>
   <soapenv:Body>
      <b2b:EchoRequest>
         <b2b:HeaderSupplier>
            <b2b:MessageID>{$messageId}</b2b:MessageID>
            <b2b:SupplierID>9934:{$this->context->supplierOib}</b2b:SupplierID>
            <b2b:ERPID>LARAVEL_RACUNI_APP</b2b:ERPID>
            <b2b:MessageType>9999</b2b:MessageType>
         </b2b:HeaderSupplier>
         <b2b:Data>
            <b2b:EchoData>
               <b2b:Echo>{$message}</b2b:Echo>
            </b2b:EchoData>
         </b2b:Data>
      </b2b:EchoRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
}
```

**Iste namespace promjene** treba napraviti i na `buildSendInvoiceRequest()`, `buildGetStatusRequest()` - samo dodati iste 3 `xmlns` linije.  
Ukloniti `<b2b:AdditionalSupplierID>HR99:00001</b2b:AdditionalSupplierID>` iz `buildSendInvoiceRequest()` - to polje ne postoji u registru.

### 2c. Ispraviti endpoint u `sendSoapViaHttp()`

Trenutno skida `?wsdl` iz WSDL URL-a. Nakon što FINA da pravi URL, ide direktno u `.env`:

```php
// U config/eracun.php - demo:
'wsdl_url' => env('ERACUN_DEMO_URL', 'https://eracun-demo.fina.hr/...?wsdl'),
```

---

## 3. Izmjena u `config/eracun.php`

Kada FINA pošalje demo URL, samo update `.env`:

```
ERACUN_DEMO_URL=https://<url-koji-da-fina>/axis2/services/SendB2BOutgoingInvoice?wsdl
```

Nema promjene koda.

---

## Sažetak promjena

| Datoteka | Što se mijenja | Veličina promjene |
|---------|---------------|-------------------|
| `XmlSigner.php` | Nova metoda `signSoapEnvelope()` (~55 linija) | Dodaje se, ne mijenja postojeće |
| `FinaEracunClient.php` | 1 linija u `sendSoapRequest()` + namespace-ovi u `buildXxxRequest()` metodama | Minimalno |
| `.env` | Novi URL kada FINA da | Nije kod |

Sve ostalo - `CertificateLoader`, `EracunContext`, `UblInvoiceGenerator`, `EracunService` - **ostaje netaknuto**.

---

*Blocker: Ovo se može implementirati odmah, ali se ne može testirati dok FINA ne aktivira OIB putem Pristupnice i ne da URL.*
