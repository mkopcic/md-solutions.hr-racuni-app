<?php

namespace App\Services\EracunFina;

/**
 * Value Object - drži sve e-Račun podatke
 */
class EracunContext
{
    public function __construct(
        public readonly string $environment,           // 'demo' ili 'production'
        public readonly string $wsdlUrl,              // SOAP WSDL endpoint
        public readonly string $certPath,             // Path do .p12 certifikata
        public readonly string $certPassword,         // Password za certifikat
        public readonly string $supplierOib,          // OIB dobavljača
        public readonly string $supplierName,         // Ime dobavljača
        public readonly string $supplierAddress,      // Adresa dobavljača
        public readonly string $supplierCity,         // Grad dobavljača
        public readonly string $supplierPostalCode,   // Poštanski broj
        public readonly string $supplierIban,         // IBAN za plaćanje
    ) {}
    
    public static function fromConfig(): self
    {
        $env = config('eracun.environment', 'demo');
        
        return new self(
            environment: $env,
            wsdlUrl: config("eracun.{$env}.wsdl_url"),
            certPath: config("eracun.{$env}.cert_path"),
            certPassword: config("eracun.{$env}.cert_password"),
            supplierOib: config('eracun.supplier.oib'),
            supplierName: config('eracun.supplier.name'),
            supplierAddress: config('eracun.supplier.address'),
            supplierCity: config('eracun.supplier.city'),
            supplierPostalCode: config('eracun.supplier.postal_code'),
            supplierIban: config('eracun.supplier.iban'),
        );
    }
    
    public function isDemoEnvironment(): bool
    {
        return $this->environment === 'demo';
    }
    
    public function isProductionEnvironment(): bool
    {
        return $this->environment === 'production';
    }
}
