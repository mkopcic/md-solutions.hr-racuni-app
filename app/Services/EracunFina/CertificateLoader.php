<?php

namespace App\Services\EracunFina;

use Exception;

/**
 * Učitava i parsira PKCS#12 (.p12) ili PEM (.pem) certifikat za XMLDSig potpis
 */
class CertificateLoader
{
    protected string $certPath;

    protected string $password;

    protected ?string $keyPath = null;

    protected ?array $certData = null;

    public function __construct(string $certPath, string $password, ?string $keyPath = null)
    {
        $this->certPath = $certPath;
        $this->password = $password;
        $this->keyPath = $keyPath;
    }

    /**
     * Učitava certifikat i vraća parsiran sadržaj
     */
    public function load(): array
    {
        if ($this->certData !== null) {
            return $this->certData;
        }

        // Logging za debugging
        logger()->info('[CertificateLoader] Učitavanje certifikata', [
            'cert_path' => $this->certPath,
            'key_path' => $this->keyPath,
            'file_exists' => file_exists($this->certPath),
            'password_length' => strlen($this->password),
        ]);

        if (! file_exists($this->certPath)) {
            throw new Exception("Certifikat ne postoji: {$this->certPath}");
        }

        // Detektuj format po ekstenziji
        $extension = strtolower(pathinfo($this->certPath, PATHINFO_EXTENSION));

        if ($extension === 'pem') {
            $this->certData = $this->loadPemFormat();
        } else {
            $this->certData = $this->loadPkcs12Format();
        }

        return $this->certData;
    }

    /**
     * Učitava PEM format certifikata
     */
    protected function loadPemFormat(): array
    {
        $certContent = file_get_contents($this->certPath);
        if ($certContent === false) {
            throw new Exception("Nije moguće pročitati PEM certifikat: {$this->certPath}");
        }

        logger()->info('[CertificateLoader] PEM certifikat pročitan', [
            'cert_file_size' => strlen($certContent),
        ]);

        // Ako je keyPath zasebno naveden, učitaj ga
        $keyContent = $certContent; // Default: isti fajl sadrži i cert i key
        if ($this->keyPath && file_exists($this->keyPath)) {
            $keyContent = file_get_contents($this->keyPath);
            if ($keyContent === false) {
                throw new Exception("Nije moguće pročitati PEM key: {$this->keyPath}");
            }
            logger()->info('[CertificateLoader] PEM key pročitan', [
                'key_file_size' => strlen($keyContent),
            ]);
        }

        // Dekriptuj encrypted key ako ima password
        $pkey = openssl_pkey_get_private($keyContent, $this->password);
        if ($pkey === false) {
            // Probaj bez passworda
            $pkey = openssl_pkey_get_private($keyContent);
            if ($pkey === false) {
                $opensslError = openssl_error_string();
                throw new Exception("Nije moguće učitati private key iz PEM-a. OpenSSL error: {$opensslError}");
            }
        }

        // Eksportuj key u PEM string format
        if (! openssl_pkey_export($pkey, $pkeyString)) {
            throw new Exception('Nije moguće eksportovati private key: '.openssl_error_string());
        }

        return [
            'cert' => $certContent,
            'pkey' => $pkeyString,
            'extracerts' => null,
        ];
    }

    /**
     * Učitava PKCS#12 format (.p12)
     */
    protected function loadPkcs12Format(): array
    {
        $p12Content = file_get_contents($this->certPath);

        if ($p12Content === false) {
            throw new Exception("Nije moguće pročitati certifikat: {$this->certPath}");
        }

        logger()->info('[CertificateLoader] PKCS#12 certifikat pročitan', [
            'file_size' => strlen($p12Content),
        ]);

        // Clear previous OpenSSL errors
        while (openssl_error_string() !== false);

        $certs = [];
        if (! openssl_pkcs12_read($p12Content, $certs, $this->password)) {
            $opensslError = openssl_error_string();

            logger()->error('[CertificateLoader] OpenSSL PKCS12 read failed', [
                'openssl_error' => $opensslError,
                'password_length' => strlen($this->password),
                'file_size' => strlen($p12Content),
            ]);

            throw new Exception("Nije moguće parsirati PKCS#12 certifikat. OpenSSL error: {$opensslError}");
        }

        if (! isset($certs['pkey']) || ! isset($certs['cert'])) {
            throw new Exception('Neispravan PKCS#12 format - nedostaje private key ili certifikat.');
        }

        return $certs;
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
            throw new Exception('Nije moguće pročitati certifikat informacije.');
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
