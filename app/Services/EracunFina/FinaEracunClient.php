<?php

namespace App\Services\EracunFina;

use App\Models\Invoice;
use Exception;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

/**
 * SOAP klijent za komunikaciju s FINA e-Račun web servisom
 */
class FinaEracunClient
{
    protected EracunContext $context;

    protected CertificateLoader $certLoader;

    protected XmlSigner $xmlSigner;

    protected ?SoapClient $soapClient = null;

    public function __construct(
        EracunContext $context,
        CertificateLoader $certLoader,
        XmlSigner $xmlSigner
    ) {
        $this->context = $context;
        $this->certLoader = $certLoader;
        $this->xmlSigner = $xmlSigner;
    }

    /**
     * Inicijalizacija SOAP klijenta
     */
    protected function getSoapClient(): SoapClient
    {
        if ($this->soapClient !== null) {
            return $this->soapClient;
        }

        try {
            $this->soapClient = new SoapClient($this->context->wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'local_cert' => $this->context->certPath,
                'passphrase' => $this->context->certPassword,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => false,
                        'cafile' => $this->context->certPath,
                    ],
                ]),
            ]);

            return $this->soapClient;
        } catch (SoapFault $e) {
            Log::error('FINA e-Račun SOAP klijent greška', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'wsdl' => $this->context->wsdlUrl,
            ]);

            throw new Exception("Greška pri inicijalizaciji SOAP klijenta: {$e->getMessage()}");
        }
    }

    /**
     * Test EchoMsg - za provjeru rada servisa
     */
    public function testEcho(string $message = 'Test poruka'): array
    {
        $messageId = uniqid('ECHO_', true);

        $soapEnvelope = $this->buildEchoRequest($messageId, $message);

        try {
            $response = $this->sendSoapRequest('EchoMsg', $soapEnvelope);

            return [
                'success' => true,
                'message_id' => $messageId,
                'response' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Slanje računa prema FINA-i
     */
    public function sendInvoice(Invoice $invoice, string $ublXml): array
    {
        $messageId = uniqid('INV_', true);

        // Base64 encode UBL XML-a
        $encodedUbl = base64_encode($ublXml);

        // Kreiraj SOAP request
        $soapEnvelope = $this->buildSendInvoiceRequest($messageId, $invoice, $encodedUbl);

        try {
            $response = $this->sendSoapRequest('SendB2BOutgoingInvoiceMsg', $soapEnvelope);

            return $this->parseSendInvoiceResponse($response);
        } catch (Exception $e) {
            Log::error('FINA e-Račun slanje neuspješno', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_invoice_number,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Dohvat statusa računa
     */
    public function getInvoiceStatus(string $invoiceNumber, int $year): array
    {
        $messageId = uniqid('STATUS_', true);

        $soapEnvelope = $this->buildGetStatusRequest($messageId, $invoiceNumber, $year);

        try {
            $response = $this->sendSoapRequest('GetB2BOutgoingInvoiceStatusMsg', $soapEnvelope);

            return $this->parseStatusResponse($response);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Dohvat liste ulaznih računa
     */
    public function getIncomingInvoices(array $filters = []): array
    {
        $messageId = uniqid('INCOMING_', true);

        $soapEnvelope = $this->buildGetIncomingRequest($messageId, $filters);

        try {
            $response = $this->sendSoapRequest('GetB2BIncomingInvoiceListMsg', $soapEnvelope);

            return $this->parseIncomingListResponse($response);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Slanje SOAP requesta
     */
    protected function sendSoapRequest(string $method, string $xmlEnvelope): string
    {
        $client = $this->getSoapClient();

        try {
            // SOAP metoda poziva
            $response = $client->__doRequest(
                $xmlEnvelope,
                $this->context->wsdlUrl,
                $method,
                SOAP_1_1
            );

            if ($response === null || $response === false) {
                throw new Exception('SOAP request vratio null/false response');
            }

            return $response;
        } catch (SoapFault $e) {
            Log::error('FINA e-Račun SOAP greška', [
                'method' => $method,
                'faultcode' => $e->faultcode ?? null,
                'faultstring' => $e->faultstring ?? null,
                'detail' => $e->detail ?? null,
            ]);

            throw new Exception("SOAP greška: {$e->getMessage()}");
        }
    }

    /**
     * Gradi Echo SOAP request
     */
    protected function buildEchoRequest(string $messageId, string $message): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73">
   <soapenv:Header/>
   <soapenv:Body>
      <b2b:EchoRequest>
         <b2b:HeaderSupplier>
            <b2b:MessageID>{$messageId}</b2b:MessageID>
            <b2b:SupplierID>9934:{$this->context->supplierOib}</b2b:SupplierID>
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

    /**
     * Gradi SendInvoice SOAP request
     */
    protected function buildSendInvoiceRequest(string $messageId, Invoice $invoice, string $encodedUbl): string
    {
        $supplierInvoiceId = $invoice->full_invoice_number;
        $buyerOib = $invoice->customer->oib;

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73">
   <soapenv:Header/>
   <soapenv:Body>
      <b2b:SendB2BOutgoingInvoiceRequest>
         <b2b:HeaderSupplier>
            <b2b:MessageID>{$messageId}</b2b:MessageID>
            <b2b:SupplierID>9934:{$this->context->supplierOib}</b2b:SupplierID>
            <b2b:AdditionalSupplierID>HR99:00001</b2b:AdditionalSupplierID>
            <b2b:ERPID>LARAVEL_RACUNI_APP</b2b:ERPID>
            <b2b:MessageType>9001</b2b:MessageType>
         </b2b:HeaderSupplier>
         <b2b:Data>
            <b2b:B2BOutgoingInvoiceEnvelope>
               <b2b:XMLStandard>UBL</b2b:XMLStandard>
               <b2b:SpecificationIdentifier>urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0</b2b:SpecificationIdentifier>
               <b2b:SupplierInvoiceID>{$supplierInvoiceId}</b2b:SupplierInvoiceID>
               <b2b:BuyerID>9934:{$buyerOib}</b2b:BuyerID>
               <b2b:InvoiceEnvelope>{$encodedUbl}</b2b:InvoiceEnvelope>
            </b2b:B2BOutgoingInvoiceEnvelope>
         </b2b:Data>
      </b2b:SendB2BOutgoingInvoiceRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Gradi GetStatus SOAP request
     */
    protected function buildGetStatusRequest(string $messageId, string $invoiceNumber, int $year): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73">
   <soapenv:Header/>
   <soapenv:Body>
      <b2b:GetB2BOutgoingInvoiceStatusRequest>
         <b2b:HeaderSupplier>
            <b2b:MessageID>{$messageId}</b2b:MessageID>
            <b2b:SupplierID>9934:{$this->context->supplierOib}</b2b:SupplierID>
            <b2b:MessageType>9011</b2b:MessageType>
         </b2b:HeaderSupplier>
         <b2b:Data>
            <b2b:B2BOutgoingInvoiceStatus>
               <b2b:SupplierInvoiceID>{$invoiceNumber}</b2b:SupplierInvoiceID>
               <b2b:InvoiceYear>{$year}</b2b:InvoiceYear>
            </b2b:B2BOutgoingInvoiceStatus>
         </b2b:Data>
      </b2b:GetB2BOutgoingInvoiceStatusRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Gradi GetIncoming SOAP request
     */
    protected function buildGetIncomingRequest(string $messageId, array $filters): string
    {
        $filterXml = '';

        if (! empty($filters['status'])) {
            $filterXml .= "<b2b:InvoiceStatus><b2b:StatusCode>{$filters['status']}</b2b:StatusCode></b2b:InvoiceStatus>";
        }

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $filterXml .= "<b2b:DateRange><b2b:From>{$filters['date_from']}</b2b:From><b2b:To>{$filters['date_to']}</b2b:To></b2b:DateRange>";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73">
   <soapenv:Header/>
   <soapenv:Body>
      <b2b:GetB2BIncomingInvoiceListRequest>
         <b2b:HeaderBuyer>
            <b2b:MessageID>{$messageId}</b2b:MessageID>
            <b2b:BuyerID>9934:{$this->context->supplierOib}</b2b:BuyerID>
            <b2b:MessageType>9101</b2b:MessageType>
         </b2b:HeaderBuyer>
         <b2b:Data>
            <b2b:B2BIncomingInvoiceList>
               <b2b:Filter>{$filterXml}</b2b:Filter>
            </b2b:B2BIncomingInvoiceList>
         </b2b:Data>
      </b2b:GetB2BIncomingInvoiceListRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Parsira SendInvoice response
     */
    protected function parseSendInvoiceResponse(string $response): array
    {
        // TODO: Parsirati SOAP response i vratiti strukturirane podatke
        // Za sada vraćamo raw response

        $success = str_contains($response, 'ACCEPTED') || str_contains($response, '<b2b:AckStatus>10</b2b:AckStatus>');

        return [
            'success' => $success,
            'response' => $response,
            'status' => $success ? 'ACCEPTED' : 'REJECTED',
        ];
    }

    /**
     * Parsira Status response
     */
    protected function parseStatusResponse(string $response): array
    {
        // TODO: Parsirati SOAP response
        return [
            'success' => true,
            'response' => $response,
        ];
    }

    /**
     * Parsira Incoming list response
     */
    protected function parseIncomingListResponse(string $response): array
    {
        // TODO: Parsirati SOAP response
        return [
            'success' => true,
            'response' => $response,
        ];
    }
}
