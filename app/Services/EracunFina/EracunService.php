<?php

namespace App\Services\EracunFina;

use App\Models\Invoice;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Glavni servis za e-Račun - orkestrira cijeli proces slanja računa
 */
class EracunService
{
    protected EracunContext $context;
    protected CertificateLoader $certLoader;
    protected UblInvoiceGenerator $ublGenerator;
    protected XmlSigner $xmlSigner;
    protected FinaEracunClient $client;

    public function __construct()
    {
        $this->context = EracunContext::fromConfig();
        $this->certLoader = new CertificateLoader(
            $this->context->certPath,
            $this->context->certPassword
        );
        $this->ublGenerator = new UblInvoiceGenerator($this->context);
        $this->xmlSigner = new XmlSigner($this->certLoader, $this->context);
        $this->client = new FinaEracunClient($this->context, $this->certLoader, $this->xmlSigner);
    }

    /**
     * Pošalji račun prema FINA e-Račun sustavu
     */
    public function sendInvoice(Invoice $invoice): array
    {
        try {
            // 1. Provjeri certifikat
            if (!$this->certLoader->isValid()) {
                throw new Exception('Certifikat nije validan ili je istekao.');
            }

            // 2. Generiraj UBL 2.1 XML
            Log::info('e-Račun: Generiranje UBL XML-a', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_invoice_number,
            ]);

            $ublXml = $this->ublGenerator->generate($invoice);

            // 3. Digitalno potpiši UBL XML
            Log::info('e-Račun: Potpisivanje XML-a');
            $signedUbl = $this->xmlSigner->sign($ublXml);

            // 4. Pošalji prema FINA-i
            Log::info('e-Račun: Slanje prema FINA web servisu');
            $response = $this->client->sendInvoice($invoice, $signedUbl);

            // 5. Log rezultata
            if ($response['success']) {
                Log::info('e-Račun: Uspješno poslan', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->full_invoice_number,
                    'response' => $response,
                ]);
            } else {
                Log::error('e-Račun: Slanje neuspješno', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->full_invoice_number,
                    'error' => $response['error'] ?? 'Unknown error',
                ]);
            }

            return [
                'success' => $response['success'],
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_invoice_number,
                'ubl_xml' => $ublXml,
                'signed_xml' => $signedUbl,
                'response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('e-Račun: Exception prilikom slanja', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_invoice_number,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_invoice_number,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Provjerava status računa
     */
    public function getInvoiceStatus(string $invoiceNumber, ?int $year = null): array
    {
        try {
            $year = $year ?? now()->year;

            $response = $this->client->getInvoiceStatus($invoiceNumber, $year);

            return [
                'success' => $response['success'],
                'invoice_number' => $invoiceNumber,
                'year' => $year,
                'response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('e-Račun: Greška kod dohvata statusa', [
                'invoice_number' => $invoiceNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'invoice_number' => $invoiceNumber,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Dohvaća ulazne račune
     */
    public function getIncomingInvoices(array $filters = []): array
    {
        try {
            $response = $this->client->getIncomingInvoices($filters);

            return [
                'success' => $response['success'],
                'filters' => $filters,
                'response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('e-Račun: Greška kod dohvata ulaznih računa', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test Echo - za provjeru rada servisa
     */
    public function testEcho(string $message = 'Test poruka'): array
    {
        try {
            $response = $this->client->testEcho($message);

            return [
                'success' => $response['success'],
                'message' => $message,
                'response' => $response,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Provjerava konfiguraciju i certifikat
     */
    public function diagnostics(): array
    {
        $diagnostics = [];

        // 1. Provjeri konfiguraciju
        $diagnostics['config'] = [
            'environment' => $this->context->environment,
            'wsdl_url' => $this->context->wsdlUrl,
            'cert_path' => $this->context->certPath,
            'cert_exists' => file_exists($this->context->certPath),
            'supplier_oib' => $this->context->supplierOib,
        ];

        // 2. Provjeri certifikat
        try {
            $certInfo = $this->certLoader->getCertificateInfo();
            $diagnostics['certificate'] = [
                'valid' => $this->certLoader->isValid(),
                'subject' => $certInfo['subject'] ?? null,
                'issuer' => $certInfo['issuer'] ?? null,
                'valid_from' => $certInfo['valid_from'] ? date('Y-m-d H:i:s', $certInfo['valid_from']) : null,
                'valid_to' => $certInfo['valid_to'] ? date('Y-m-d H:i:s', $certInfo['valid_to']) : null,
            ];
        } catch (Exception $e) {
            $diagnostics['certificate'] = [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }

        // 3. Provjeri SOAP klijent
        try {
            $echoResult = $this->testEcho('Diagnostics test');
            $diagnostics['soap_client'] = [
                'working' => $echoResult['success'],
                'response' => $echoResult,
            ];
        } catch (Exception $e) {
            $diagnostics['soap_client'] = [
                'working' => false,
                'error' => $e->getMessage(),
            ];
        }

        // 4. Provjeri robrichards/xmlseclibs
        $diagnostics['xmlseclibs'] = [
            'installed' => class_exists(\RobRichards\XMLSecLibs\XMLSecurityDSig::class),
        ];

        return $diagnostics;
    }

    /**
     * Generira UBL XML za pregled (bez slanja)
     */
    public function generateUblPreview(Invoice $invoice): string
    {
        return $this->ublGenerator->generate($invoice);
    }

    /**
     * Potpisuje XML za pregled (bez slanja)
     */
    public function signXmlPreview(string $xml): string
    {
        return $this->xmlSigner->sign($xml);
    }
}
