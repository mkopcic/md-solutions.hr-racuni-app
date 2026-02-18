<?php

namespace App\Services\EracunFina;

use Exception;

/**
 * Učitava i parsira PKCS#12 (.p12) certifikat za XMLDSig potpis
 */
class CertificateLoader
{
    protected string $certPath;
    protected string $password;
    protected ?array $certData = null;
    
    public function __construct(string $certPath, string $password)
    {
        $this->certPath = $certPath;
        $this->password = $password;
    }
    
    /**
     * Učitava certifikat i vraća parsiran sadržaj
     */
    public function load(): array
    {
        if ($this->certData !== null) {
            return $this->certData;
        }
        
        if (!file_exists($this->certPath)) {
            throw new Exception("Certifikat ne postoji: {$this->certPath}");
        }
        
        $p12Content = file_get_contents($this->certPath);
        
        if ($p12Content === false) {
            throw new Exception("Nije moguće pročitati certifikat: {$this->certPath}");
        }
        
        $certs = [];
        if (!openssl_pkcs12_read($p12Content, $certs, $this->password)) {
            throw new Exception("Nije moguće parsirati PKCS#12 certifikat. Provjeri password.");
        }
        
        if (!isset($certs['pkey']) || !isset($certs['cert'])) {
            throw new Exception("Neispravan PKCS#12 format - nedostaje private key ili certifikat.");
        }
        
        $this->certData = $certs;
        
        return $this->certData;
    }
    
    /**
     * Vraća private key
     */
    public function getPrivateKey(): string
    {
        $data = $this->load();
        return $data['pkey'];
    }
    
    /**
     * Vraća javni certifikat
     */
    public function getCertificate(): string
    {
        $data = $this->load();
        return $data['cert'];
    }
    
    /**
     * Vraća CA chain (opciono)
     */
    public function getCertificateChain(): ?array
    {
        $data = $this->load();
        return $data['extracerts'] ?? null;
    }
    
    /**
     * Izvlači informacije iz certifikata
     */
    public function getCertificateInfo(): array
    {
        $cert = $this->getCertificate();
        $certResource = openssl_x509_read($cert);
        
        if ($certResource === false) {
            throw new Exception("Nije moguće pročitati certifikat informacije.");
        }
        
        $info = openssl_x509_parse($certResource);
        
        return [
            'subject' => $info['subject'] ?? [],
            'issuer' => $info['issuer'] ?? [],
            'valid_from' => $info['validFrom_time_t'] ?? null,
            'valid_to' => $info['validTo_time_t'] ?? null,
            'serial_number' => $info['serialNumber'] ?? null,
        ];
    }
    
    /**
     * Provjerava da li je certifikat validan
     */
    public function isValid(): bool
    {
        try {
            $info = $this->getCertificateInfo();
            $now = time();
            
            if (isset($info['valid_from']) && $now < $info['valid_from']) {
                return false; // Certifikat još nije validan
            }
            
            if (isset($info['valid_to']) && $now > $info['valid_to']) {
                return false; // Certifikat je istekao
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
