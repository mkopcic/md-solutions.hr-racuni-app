<?php

namespace App\Services\EracunFina;

use DOMDocument;
use DOMXPath;
use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Potpisuje XML dokumente koristeći XMLDSig (digitalni potpis)
 * Koristi robrichards/xmlseclibs biblioteku
 */
class XmlSigner
{
    protected CertificateLoader $certLoader;

    protected EracunContext $context;

    public function __construct(CertificateLoader $certLoader, EracunContext $context)
    {
        $this->certLoader = $certLoader;
        $this->context = $context;
    }

    /**
     * Potpisuje XML string i vraća potpisani XML
     */
    public function sign(string $xml): string
    {
        if (! class_exists(XMLSecurityDSig::class)) {
            throw new Exception('robrichards/xmlseclibs biblioteka nije instalirana. Pokreni: composer require robrichards/xmlseclibs');
        }

        $doc = new DOMDocument;
        $doc->loadXML($xml);

        // Kreiraj XMLSecurityDSig objekt
        $objDSig = new XMLSecurityDSig;
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        // Dodaj Reference na cijeli dokument
        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['force_uri' => true]
        );

        // Kreiraj privatni ključ za potpis
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($this->certLoader->getPrivateKey());

        // Potpiši dokument
        $objDSig->sign($objKey);

        // Dodaj certifikat u potpis
        $objDSig->add509Cert($this->certLoader->getCertificate());

        // Dodaj potpis u dokument
        $objDSig->appendSignature($doc->documentElement);

        return $doc->saveXML();
    }

    /**
     * Provjerava da li je XML potpisan
     */
    public function isSigned(string $xml): bool
    {
        try {
            $doc = new DOMDocument;
            $doc->loadXML($xml);

            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);

            $signatures = $xpath->query('//ds:Signature');

            return $signatures && $signatures->length > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Potpisuje SOAP envelope s WS-Security standardom.
     * Stavlja potpis u SOAP Header (za razliku od sign() koji potpisuje UBL Body).
     */
    public function signSoapEnvelope(string $soapXml): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($soapXml);

        $nsEnv = 'http://schemas.xmlsoap.org/soap/envelope/';
        $nsWsse = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        $nsWsu = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
        $nsX509 = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3';
        $nsEnc = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('soapenv', $nsEnv);

        $header = $xpath->query('/soapenv:Envelope/soapenv:Header')->item(0);
        $body = $xpath->query('/soapenv:Envelope/soapenv:Body')->item(0);

        if ($header === null || $body === null) {
            throw new Exception('Neispravan SOAP envelope - nedostaje Header ili Body.');
        }

        // Body treba wsu:Id da Signature može referencirati URI="#Body"
        $body->setAttributeNS($nsWsu, 'wsu:Id', 'Body');

        // Certifikat u Base64 DER formatu za BinarySecurityToken
        $certPem = $this->certLoader->getCertificate();
        $certDer = base64_encode(
            base64_decode(preg_replace('/-----[^-]+-----|[\r\n\s]/', '', $certPem))
        );

        // <wsse:Security> u Headeru
        $security = $doc->createElementNS($nsWsse, 'wsse:Security');
        $security->setAttributeNS($nsEnv, 'soapenv:mustUnderstand', '1');

        // <wsse:BinarySecurityToken>
        $bst = $doc->createElementNS($nsWsse, 'wsse:BinarySecurityToken', $certDer);
        $bst->setAttributeNS($nsWsu, 'wsu:Id', 'X509Token');
        $bst->setAttribute('ValueType', $nsX509);
        $bst->setAttribute('EncodingType', $nsEnc);
        $security->appendChild($bst);

        $header->appendChild($security);

        // Potpiši Body element
        $objDSig = new XMLSecurityDSig;
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference(
            $body,
            XMLSecurityDSig::SHA256,
            [XMLSecurityDSig::EXC_C14N],
            ['overwrite' => false]
        );

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($this->certLoader->getPrivateKey());
        $objDSig->sign($objKey);

        // KeyInfo - SecurityTokenReference na BinarySecurityToken
        // Mora biti kreiran u istom dokumentu kao sigNode (interni XMLSecLibs dokument)
        $sigDoc = $objDSig->sigNode->ownerDocument;
        $keyInfo = $sigDoc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:KeyInfo');
        $strElem = $sigDoc->createElementNS($nsWsse, 'wsse:SecurityTokenReference');
        $refElem = $sigDoc->createElementNS($nsWsse, 'wsse:Reference');
        $refElem->setAttribute('URI', '#X509Token');
        $refElem->setAttribute('ValueType', $nsX509);
        $strElem->appendChild($refElem);
        $keyInfo->appendChild($strElem);
        $objDSig->sigNode->appendChild($keyInfo);

        // Potpis ide u Security header, ne u Body
        $objDSig->appendSignature($security);

        return $doc->saveXML();
    }

    /**
     * Verificira potpis na XML dokumentu
     */
    public function verify(string $xml): bool
    {
        try {
            $doc = new DOMDocument;
            $doc->loadXML($xml);

            $objDSig = new XMLSecurityDSig;

            $objXMLSignature = $objDSig->locateSignature($doc);
            if (! $objXMLSignature) {
                return false;
            }

            $objDSig->canonicalizeSignedInfo();

            $objKey = $objDSig->locateKey();
            if (! $objKey) {
                return false;
            }

            $objKeyInfo = XMLSecurityDSig::staticLocateKeyInfo($objKey, $objXMLSignature);
            if (! $objKeyInfo->key) {
                return false;
            }

            return $objDSig->verify($objKey) === 1;
        } catch (Exception $e) {
            return false;
        }
    }
}
