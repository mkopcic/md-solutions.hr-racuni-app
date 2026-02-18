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
        if (!class_exists(XMLSecurityDSig::class)) {
            throw new Exception("robrichards/xmlseclibs biblioteka nije instalirana. Pokreni: composer require robrichards/xmlseclibs");
        }
        
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        
        // Kreiraj XMLSecurityDSig objekt
        $objDSig = new XMLSecurityDSig();
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
            $doc = new DOMDocument();
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
     * Verificira potpis na XML dokumentu
     */
    public function verify(string $xml): bool
    {
        try {
            $doc = new DOMDocument();
            $doc->loadXML($xml);
            
            $objDSig = new XMLSecurityDSig();
            
            $objXMLSignature = $objDSig->locateSignature($doc);
            if (!$objXMLSignature) {
                return false;
            }
            
            $objDSig->canonicalizeSignedInfo();
            
            $objKey = $objDSig->locateKey();
            if (!$objKey) {
                return false;
            }
            
            $objKeyInfo = XMLSecurityDSig::staticLocateKeyInfo($objKey, $objXMLSignature);
            if (!$objKeyInfo->key) {
                return false;
            }
            
            return $objDSig->verify($objKey) === 1;
        } catch (Exception $e) {
            return false;
        }
    }
}
