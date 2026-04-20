<?php

namespace App\Livewire\Eracun;

use App\Services\EracunFina\CertificateLoader;
use Exception;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    // --- Card 1: Konfiguracija integracije ---
    public string $environment = 'demo';

    public string $demoUrl = '';

    public string $supplierOib = '';

    public string $supplierName = '';

    public string $supplierAddress = '';

    public string $supplierCity = '';

    public string $supplierPostalCode = '';

    public string $supplierIban = '';

    // --- Card 2: Certificate Manager ---
    public ?array $certInfo = null;

    public bool $certExists = false;

    public bool $certValid = false;

    public int $certDaysLeft = 0;

    public $p12File = null;

    public string $p12Password = '';

    public bool $showUploadForm = false;

    public function mount(): void
    {
        $this->loadConfig();
        $this->loadCertInfo();
    }

    protected function loadConfig(): void
    {
        $this->environment = config('eracun.environment', 'demo');
        $this->demoUrl = config('eracun.demo.wsdl_url') ?? '';
        $this->supplierOib = config('eracun.supplier.oib') ?? '';
        $this->supplierName = config('eracun.supplier.name') ?? '';
        $this->supplierAddress = config('eracun.supplier.address') ?? '';
        $this->supplierCity = config('eracun.supplier.city') ?? '';
        $this->supplierPostalCode = config('eracun.supplier.postal_code') ?? '';
        $this->supplierIban = config('eracun.supplier.iban') ?? '';
    }

    protected function loadCertInfo(): void
    {
        $certPath = config('eracun.demo.cert_path');
        $certPassword = config('eracun.demo.cert_password') ?? '';

        $this->certExists = $certPath && file_exists($certPath);

        if (! $this->certExists) {
            return;
        }

        try {
            $loader = new CertificateLoader($certPath, $certPassword);
            $info = $loader->getCertificateInfo();
            $this->certValid = $loader->isValid();

            $validTo = $info['valid_to'] ?? null;
            $this->certDaysLeft = $validTo ? (int) ceil(($validTo - time()) / 86400) : 0;

            $cert = $loader->getCertificate();
            $x509 = openssl_x509_read($cert);
            $fingerprint = '';
            if ($x509 !== false) {
                $fp = openssl_x509_fingerprint($x509, 'sha1');
                if ($fp !== false) {
                    $fingerprint = strtoupper(implode(':', str_split($fp, 2)));
                }
            }

            $this->certInfo = [
                'path' => $certPath,
                'subject_cn' => $info['subject']['CN'] ?? '',
                'subject_o' => $info['subject']['O'] ?? '',
                'issuer_cn' => $info['issuer']['CN'] ?? '',
                'serial' => $info['serial_number'] ?? '',
                'valid_from' => $info['valid_from'] ? date('d.m.Y', $info['valid_from']) : '',
                'valid_to' => $validTo ? date('d.m.Y', $validTo) : '',
                'fingerprint' => $fingerprint,
                'filename' => basename($certPath),
            ];
        } catch (Exception $e) {
            $this->certValid = false;
            $this->certInfo = null;
        }
    }

    public function saveConfig(): void
    {
        $this->validate([
            'environment' => 'required|in:demo,production',
            'supplierOib' => 'required|digits:11',
            'supplierName' => 'required|string|max:255',
            'supplierAddress' => 'required|string|max:255',
            'supplierCity' => 'required|string|max:100',
            'supplierPostalCode' => 'required|string|max:10',
            'supplierIban' => 'required|regex:/^HR[0-9]{19}$/',
            'demoUrl' => 'nullable|url',
        ], [
            'supplierOib.digits' => 'OIB mora imati točno 11 znamenki.',
            'supplierIban.regex' => 'IBAN mora biti u formatu HR + 19 znamenki.',
            'demoUrl.url' => 'Demo URL mora biti valjana URL adresa.',
        ]);

        $this->writeEnvValues([
            'ERACUN_ENVIRONMENT' => $this->environment,
            'ERACUN_DEMO_URL' => $this->demoUrl,
            'ERACUN_SUPPLIER_OIB' => $this->supplierOib,
            'ERACUN_SUPPLIER_NAME' => $this->supplierName,
            'ERACUN_SUPPLIER_ADDRESS' => $this->supplierAddress,
            'ERACUN_SUPPLIER_CITY' => $this->supplierCity,
            'ERACUN_SUPPLIER_POSTAL_CODE' => $this->supplierPostalCode,
            'ERACUN_SUPPLIER_IBAN' => $this->supplierIban,
        ]);

        $this->clearConfigCache();
        $this->loadCertInfo();

        session()->flash('config_saved', 'Konfiguracija uspješno spremljena.');
    }

    public function uploadCertificate(): void
    {
        $this->validate([
            'p12File' => 'required|file|max:2048',
            'p12Password' => 'nullable|string',
        ], [
            'p12File.required' => 'Odaberite .p12 certifikat.',
            'p12File.max' => 'Certifikat ne smije biti veći od 2 MB.',
        ]);

        $certDir = storage_path('certificates');
        if (! is_dir($certDir)) {
            mkdir($certDir, 0755, true);
        }

        // Testiraj p12 s passwordom PRIJE pohrane
        $tmpPath = $this->p12File->getRealPath();
        $p12Content = file_get_contents($tmpPath);
        $certs = [];

        if (! openssl_pkcs12_read($p12Content, $certs, $this->p12Password ?? '')) {
            $this->addError('p12File', 'Nije moguće otvoriti certifikat. Provjeri password i format datoteke (.p12).');

            return;
        }

        // Konvertiraj p12 → pem i spremi
        $originalName = $this->p12File->getClientOriginalName();
        $pemFilename = pathinfo($originalName, PATHINFO_FILENAME).'.pem';
        $pemPath = $certDir.'/'.$pemFilename;

        $pemContent = '';
        openssl_x509_export($certs['cert'], $certOut);
        openssl_pkey_export($certs['pkey'], $keyOut);
        $pemContent = $certOut.$keyOut;

        file_put_contents($pemPath, $pemContent);
        chmod($pemPath, 0600);

        $this->writeEnvValues([
            'ERACUN_DEMO_CERT_PATH' => 'storage/certificates/'.$pemFilename,
            'ERACUN_DEMO_CERT_PASSWORD' => '',
        ]);

        $this->clearConfigCache();

        $this->p12File = null;
        $this->p12Password = '';
        $this->showUploadForm = false;

        $this->loadCertInfo();

        session()->flash('cert_saved', 'Certifikat uspješno učitan i pohranjen.');
    }

    protected function writeEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $needsQuotes = str_contains($value, ' ') || str_contains($value, '#');
            $escaped = $needsQuotes ? '"'.$value.'"' : $value;

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $content);
            } else {
                $content .= "\n{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, $content);
    }

    protected function clearConfigCache(): void
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (Exception) {
            // ignoriramo ako ne može
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.eracun.settings')
            ->layout('components.layouts.app', ['title' => 'e-Račun Postavke']);
    }
}
