<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\EracunFina\CertificateLoader;
use App\Services\EracunFina\EracunContext;
use App\Services\EracunFina\EracunService;
use App\Services\EracunFina\UblInvoiceGenerator;
use App\Services\EracunFina\XmlSigner;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create([
        'oib' => '12345678901',
        'name' => 'Test Kupac d.o.o.',
        'address' => 'Testna ulica 1',
        'city' => 'Zagreb',
    ]);
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'invoice_number' => '1',
        'invoice_year' => 2026,
        'invoice_type' => 'invoice',
        'issue_date' => '2026-04-18',
        'delivery_date' => '2026-04-18',
        'due_date' => '2026-05-18',
        'subtotal' => 100.00,
        'tax_total' => 25.00,
        'total_amount' => 125.00,
        'payment_method' => 'virman',
    ]);

    // Postavi e-Račun konfiguraciju za testove
    config([
        'eracun.environment' => 'demo',
        'eracun.demo.wsdl_url' => 'https://cistest.apis-it.hr:8449/EracunServiceTest',
        'eracun.demo.cert_path' => storage_path('certificates/86058362621.A.4.pem'),
        'eracun.demo.cert_password' => null,
        'eracun.demo.ca_cert_path' => '',
        'eracun.supplier.oib' => '86058362621',
        'eracun.supplier.name' => 'Test Dobavljač',
        'eracun.supplier.address' => 'Testna ulica 1',
        'eracun.supplier.city' => 'Zagreb',
        'eracun.supplier.postal_code' => '10000',
        'eracun.supplier.iban' => 'HR1210010051863000160',
    ]);
});

test('UBL generator kreira validan XML s Invoice elementom', function () {
    $context = EracunContext::fromConfig();
    $generator = new UblInvoiceGenerator($context);

    $ublXml = $generator->generate($this->invoice->load('customer', 'items'));

    expect($ublXml)->toBeString()->not->toBeEmpty();

    $doc = new DOMDocument;
    $result = $doc->loadXML($ublXml);
    expect($result)->toBeTrue('UBL XML nije validan');

    expect($doc->documentElement->localName)->toBe('Invoice');
    expect($ublXml)->toContain('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
    expect($ublXml)->toContain('1-1-1'); // full_invoice_number format: {broj}-1-1
    expect($ublXml)->toContain('2026-04-18');
    expect($ublXml)->toContain($this->customer->oib);
    expect($ublXml)->toContain('86058362621');
});

test('UBL XML sadrži ispravne iznose', function () {
    $context = EracunContext::fromConfig();
    $generator = new UblInvoiceGenerator($context);

    $ublXml = $generator->generate($this->invoice->load('customer', 'items'));

    expect($ublXml)->toContain('100.00'); // subtotal
    expect($ublXml)->toContain('125.00'); // total_amount
    expect($ublXml)->toContain('EUR');
    expect($ublXml)->toContain('HR1210010051863000160'); // IBAN
});

test('XmlSigner potpisuje UBL XML i dodaje Signature element', function () {
    $certPath = storage_path('certificates/86058362621.A.4.pem');

    if (! file_exists($certPath)) {
        $this->markTestSkipped('PEM certifikat nije pronađen na: '.$certPath);
    }

    $context = EracunContext::fromConfig();
    $certLoader = new CertificateLoader($certPath, '');
    $generator = new UblInvoiceGenerator($context);
    $signer = new XmlSigner($certLoader, $context);

    $ublXml = $generator->generate($this->invoice->load('customer', 'items'));
    $signedXml = $signer->sign($ublXml);

    expect($signedXml)->toBeString()->not->toBeEmpty();

    $doc = new DOMDocument;
    $doc->loadXML($signedXml);

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

    $signatures = $xpath->query('//ds:Signature');
    expect($signatures->length)->toBeGreaterThan(0, 'XMLDSig Signature element nije pronađen u potpisanom XML-u');
});

test('XmlSigner dodaje WS-Security header u SOAP envelope', function () {
    $certPath = storage_path('certificates/86058362621.A.4.pem');

    if (! file_exists($certPath)) {
        $this->markTestSkipped('PEM certifikat nije pronađen na: '.$certPath);
    }

    $context = EracunContext::fromConfig();
    $certLoader = new CertificateLoader($certPath, '');
    $signer = new XmlSigner($certLoader, $context);

    $soapEnvelope = <<<'XML'
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
            <b2b:MessageID>TEST-123</b2b:MessageID>
            <b2b:SupplierID>9934:86058362621</b2b:SupplierID>
            <b2b:MessageType>9999</b2b:MessageType>
         </b2b:HeaderSupplier>
      </b2b:EchoRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;

    $signedSoap = $signer->signSoapEnvelope($soapEnvelope);

    expect($signedSoap)->toBeString()->not->toBeEmpty();
    expect($signedSoap)->toContain('wsse:Security');
    expect($signedSoap)->toContain('BinarySecurityToken');
    expect($signedSoap)->toContain('ds:Signature');
    expect($signedSoap)->toContain('SignedInfo');
});

test('EracunService vraća success array kad FINA prihvati račun', function () {
    $certPath = storage_path('certificates/86058362621.A.4.pem');

    if (! file_exists($certPath)) {
        $this->markTestSkipped('PEM certifikat nije pronađen na: '.$certPath);
    }

    Http::preventStrayRequests();

    $finaSuccessResponse = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73">
   <soapenv:Header/>
   <soapenv:Body>
      <b2b:SendB2BOutgoingInvoiceResponse>
         <b2b:HeaderResponse>
            <b2b:MessageID>RESP-001</b2b:MessageID>
            <b2b:MessageType>9002</b2b:MessageType>
         </b2b:HeaderResponse>
         <b2b:Data>
            <b2b:B2BOutgoingInvoiceAck>
               <b2b:SupplierInvoiceID>1-1-1</b2b:SupplierInvoiceID>
               <b2b:FinaInvoiceID>FINA-2026-001</b2b:FinaInvoiceID>
               <b2b:AckStatus>ACCEPTED</b2b:AckStatus>
               <b2b:AckStatusCode>10</b2b:AckStatusCode>
            </b2b:B2BOutgoingInvoiceAck>
         </b2b:Data>
      </b2b:SendB2BOutgoingInvoiceResponse>
   </soapenv:Body>
</soapenv:Envelope>
XML;

    Http::fake([
        '*' => Http::response($finaSuccessResponse, 200),
    ]);

    $service = new EracunService;
    $result = $service->sendInvoice($this->invoice->load('customer', 'items'));

    expect($result['success'])->toBeTrue();
    expect($result['invoice_id'])->toBe($this->invoice->id);
    expect($result['ubl_xml'])->toContain('<Invoice');
    expect($result['signed_xml'])->toContain('ds:Signature');
    expect($result['response']['response'])->toContain('ACCEPTED');
});

test('EracunService vraća failure array kad FINA odbije račun', function () {
    $certPath = storage_path('certificates/86058362621.A.4.pem');

    if (! file_exists($certPath)) {
        $this->markTestSkipped('PEM certifikat nije pronađen na: '.$certPath);
    }

    Http::preventStrayRequests();

    $finaRejectedResponse = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://www.apis-it.hr/fin/2012/types/f73">
   <soapenv:Body>
      <b2b:SendB2BOutgoingInvoiceResponse>
         <b2b:Data>
            <b2b:B2BOutgoingInvoiceAck>
               <b2b:AckStatus>REJECTED</b2b:AckStatus>
               <b2b:AckStatusCode>20</b2b:AckStatusCode>
               <b2b:ErrorDescription>Neispravan OIB dobavljača</b2b:ErrorDescription>
            </b2b:B2BOutgoingInvoiceAck>
         </b2b:Data>
      </b2b:SendB2BOutgoingInvoiceResponse>
   </soapenv:Body>
</soapenv:Envelope>
XML;

    Http::fake([
        '*' => Http::response($finaRejectedResponse, 200),
    ]);

    $service = new EracunService;
    $result = $service->sendInvoice($this->invoice->load('customer', 'items'));

    expect($result['success'])->toBeFalse();
    expect($result['invoice_id'])->toBe($this->invoice->id);
    expect($result['response']['status'])->toBe('REJECTED');
});

test('EracunService vraća failure array kod HTTP greške', function () {
    $certPath = storage_path('certificates/86058362621.A.4.pem');

    if (! file_exists($certPath)) {
        $this->markTestSkipped('PEM certifikat nije pronađen na: '.$certPath);
    }

    Http::preventStrayRequests();

    Http::fake([
        '*' => Http::response('Service Unavailable', 503),
    ]);

    $service = new EracunService;
    $result = $service->sendInvoice($this->invoice->load('customer', 'items'));

    expect($result['success'])->toBeFalse();
    expect($result['response'])->toHaveKey('error');
});

test('CertificateLoader prepoznaje validan PEM certifikat', function () {
    $certPath = storage_path('certificates/86058362621.A.4.pem');

    if (! file_exists($certPath)) {
        $this->markTestSkipped('PEM certifikat nije pronađen na: '.$certPath);
    }

    $loader = new CertificateLoader($certPath, '');

    expect($loader->isValid())->toBeTrue();
    expect($loader->getCertificate())->toBeString()->not->toBeEmpty();
    expect($loader->getPrivateKey())->toBeString()->not->toBeEmpty();
});

test('EracunContext se ispravno gradi iz konfiguracije', function () {
    $context = EracunContext::fromConfig();

    expect($context->environment)->toBe('demo');
    expect($context->supplierOib)->toBe('86058362621');
    expect($context->supplierName)->toBe('Test Dobavljač');
    expect($context->supplierIban)->toBe('HR1210010051863000160');
    expect($context->isDemoEnvironment())->toBeTrue();
    expect($context->isProductionEnvironment())->toBeFalse();
});
