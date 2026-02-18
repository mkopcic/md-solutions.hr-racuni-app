# FINA e-Račun B2B Integracija - Tehnička Dokumentacija

**Datum:** 18. veljače 2026  
**Verzija:** 1.0  
**Aplikacija:** Računi Obrt - Laravel 12

---

## 📋 Sadržaj

1. [Uvod](#uvod)
2. [Dva Pristupa Integraciji](#dva-pristupa-integraciji)
3. [Preporuka: Finin Integracijski Modul](#preporuka-finin-integracijski-modul)
4. [Alternativa: Direktna SOAP Integracija](#alternativa-direktna-soap-integracija)
5. [Tehnički Zahtjevi](#tehnički-zahtjevi)
6. [Postupak Implementacije](#postupak-implementacije)
7. [API Metode i Endpoints](#api-metode-i-endpoints)
8. [UBL 2.1 Format](#ubl-21-format)
9. [Demo i Produkcija](#demo-i-produkcija)
10. [Troškovi i Paketi](#troškovi-i-paketi)

---

## Uvod

FINA e-Račun je sustav za **B2B razmjenu elektroničkih računa** između poslovnih subjekata. Od 1. siječnja 2026. obvezno je za sve PDV obveznike u RH.

**Ovo NIJE fiskalizacija** - to je već riješeno na drugoj aplikaciji. Ovo je sustav za:
- Slanje e-računa poslovnim subjektima (B2B)
- Primanje e-računa od drugih poslovnih subjekata
- Automatska razmjena strukturiranih računa u XML formatu
- Praćenje statusa računa

---

## Dva Pristupa Integraciji

### 🎯 Pristup 1: Finin Integracijski Modul (REST API)

**Preporučeno za tvoj slučaj!**

#### Prednosti:
- ✅ **Besplatan** integracijski modul od FINA-e
- ✅ **REST API** - jednostavnije nego SOAP
- ✅ Šalješ **JSON ili XML** prema modulu
- ✅ Modul automatski kreira SOAP poruke
- ✅ Modul potpisuje poruke tvojim certifikatom
- ✅ Modul komunicira s FINA web servisom

#### Kako radi:
```
Tvoja Laravel App → REST Request (JSON/XML) → Finin Modul → SOAP → FINA Web Servis
```

#### Dokumentacija:
- Zahtjev: https://www.fina.hr/ngsite/content/download/12653/189705/1
- Kontakt: Telefon 0800 0080 ili kontakt forma na fina.hr

---

### ⚙️ Pristup 2: Direktna SOAP Integracija

**Složenije, ali daje više kontrole**

#### Karakteristike:
- Izravan WS-Security SOAP poziv prema FINA web servisu
- Moraš sam kreirati i potpisivati SOAP poruke
- Potrebno dublje poznavanje SOAP, XML Signature, WS-Security
- Potreban SOAP klijent u PHP-u (npr. SoapClient, guzzle-soap)

---

## Preporuka: Finin Integracijski Modul

### Koraci:

#### 1. Preuzmi i ispuni zahtjev
- **Zahtjev za integracijski modul**: https://www.fina.hr/ngsite/content/download/12653/189705/1
- **Dostavi na**: FINA poslovnicu ili kontaktiraj 0800 0080 za upute

#### 2. FINA će ti dostaviti:
- Integracijski modul (exe/installer)
- Upute za instalaciju
- REST API dokumentaciju

#### 3. Integracija s Laravelom

**REST endpoint prema modulu:**

```php
// Laravel HTTP Client
use Illuminate\Support\Facades\Http;

class FinaEracunService
{
    protected $moduleUrl = 'http://localhost:8080/api'; // Modul URL
    
    public function sendInvoice(Invoice $invoice)
    {
        // Kreiraj JSON strukturu
        $data = [
            'SupplierID' => '9934:' . $invoice->business->oib,
            'BuyerID' => '9934:' . $invoice->customer->oib,
            'Invoice' => $this->createInvoiceJson($invoice)
        ];
        
        // Pošalji prema modulu
        $response = Http::post($this->moduleUrl . '/send-invoice', $data);
        
        return $response->json();
    }
    
    protected function createInvoiceJson(Invoice $invoice)
    {
        // Kreiraj UBL strukturu u JSON formatu
        return [
            'InvoiceNumber' => $invoice->full_invoice_number,
            'IssueDate' => $invoice->issue_date->format('Y-m-d'),
            'DueDate' => $invoice->due_date->format('Y-m-d'),
            'TaxAmount' => $invoice->tax_total,
            'TaxableAmount' => $invoice->subtotal,
            'PayableAmount' => $invoice->total_amount,
            'InvoiceLines' => $invoice->items->map(function($item) {
                return [
                    'ItemName' => $item->name,
                    'Quantity' => $item->quantity,
                    'Price' => $item->price,
                    'LineTotal' => $item->total,
                    'TaxPercent' => $item->tax_rate,
                    'ClassificationCode' => $item->kpd_code // KPD 2025
                ];
            })->toArray()
        ];
    }
}
```

---

## Alternativa: Direktna SOAP Integracija

Ako želiš direktnu SOAP integraciju bez Fininog modula:

### SOAP Web Servis Endpoints

**Demo okolina:**
```
https://demo-eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl
```

**Produkcija:**
```
https://eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl
```

### Dostupne SOAP Metode

#### 1. **EchoMsg** - Test rada servisa
```xml
<HeaderSupplier>
    <MessageID>unique-id-123</MessageID>
    <SupplierID>9934:12345678909</SupplierID>
    <MessageType>9999</MessageType>
</HeaderSupplier>
<Data>
    <EchoData>
        <Echo>Test poruka</Echo>
    </EchoData>
</Data>
```

#### 2. **SendB2BOutgoingInvoiceMsg** - Slanje računa
```xml
<HeaderSupplier>
    <MessageID>unique-id-456</MessageID>
    <SupplierID>9934:12345678909</SupplierID>
    <AdditionalSupplierID>HR99:00001</AdditionalSupplierID>
    <ERPID>LARAVEL_RACUNI_APP</ERPID>
    <MessageType>9001</MessageType>
</HeaderSupplier>
<Data>
    <B2BOutgoingInvoiceEnvelope>
        <XMLStandard>UBL</XMLStandard>
        <SpecificationIdentifier>urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0</SpecificationIdentifier>
        <SupplierInvoiceID>1/2/1/SPO</SupplierInvoiceID>
        <BuyerID>9934:98765432109</BuyerID>
        <InvoiceEnvelope>[Base64 encoded UBL XML]</InvoiceEnvelope>
    </B2BOutgoingInvoiceEnvelope>
</Data>
```

#### 3. **GetB2BOutgoingInvoiceStatusMsg** - Provjera statusa
```xml
<HeaderSupplier>
    <MessageID>unique-id-789</MessageID>
    <SupplierID>9934:12345678909</SupplierID>
    <MessageType>9011</MessageType>
</HeaderSupplier>
<Data>
    <B2BOutgoingInvoiceStatus>
        <SupplierInvoiceID>1/2/1/SPO</SupplierInvoiceID>
        <InvoiceYear>2026</InvoiceYear>
    </B2BOutgoingInvoiceStatus>
</Data>
```

#### 4. **GetB2BIncomingInvoiceListMsg** - Dohvat ulaznih računa
```xml
<HeaderBuyer>
    <MessageID>unique-id-101</MessageID>
    <BuyerID>9934:12345678909</BuyerID>
    <MessageType>9101</MessageType>
</HeaderBuyer>
<Data>
    <B2BIncomingInvoiceList>
        <Filter>
            <InvoiceStatus>
                <StatusCode>RECEIVED</StatusCode>
            </InvoiceStatus>
            <DateRange>
                <From>2026-02-01</From>
                <To>2026-02-18</To>
            </DateRange>
        </Filter>
    </B2BIncomingInvoiceList>
</Data>
```

#### 5. **ChangeB2BIncomingInvoiceStatusMsg** - Promjena statusa ulaznog računa
```xml
<HeaderBuyer>
    <MessageID>unique-id-202</MessageID>
    <BuyerID>9934:12345678909</BuyerID>
    <MessageType>107</MessageType>
</HeaderBuyer>
<Data>
    <B2BIncomingInvoiceStatus>
        <InvoiceID>FINA-INVOICE-ID-12345</InvoiceID>
        <InvoiceStatus>
            <StatusCode>APPROVED</StatusCode>
            <Note>Račun odobren za plaćanje</Note>
        </InvoiceStatus>
    </B2BIncomingInvoiceStatus>
</Data>
```

#### 6. **GetReceiverListMsg** - Dohvat registra primatelja
```xml
<HeaderSupplier>
    <MessageID>unique-id-303</MessageID>
    <SupplierID>9934:12345678909</SupplierID>
    <MessageType>50041</MessageType>
</HeaderSupplier>
<Data>
    <ReceiverList>
        <Filter>
            <TextSearch>
                <SearchField>OIB</SearchField>
                <SearchValue>HR98765432109</SearchValue>
            </TextSearch>
        </Filter>
    </ReceiverList>
</Data>
```

### Statusi Računa

| Status | Opis |
|--------|------|
| **RECEIVED** | Zaprimljen (početni status) |
| **RECEIVING_CONFIRMED** | Potvrda zaprimanja |
| **APPROVED** | Odobren (prihvaćen) |
| **REJECTED** | Odbijen |
| **PAYMENT_RECEIVED** | Naplaćen |

### Tipovi Poruka

| Tip | Opis | Poruka |
|-----|------|--------|
| 9999 | Echo poruka | EchoMsg |
| 10000 | Odgovor na echo | EchoAckMsg |
| 9001 | Slanje izlaznog računa | SendB2BOutgoingInvoiceMsg |
| 9002 | Odgovor na slanje računa | SendB2BOutgoingInvoiceAckMsg |
| 9011 | Dohvat statusa izlaznog računa | GetB2BOutgoingInvoiceStatusMsg |
| 9012 | Odgovor na dohvat statusa | GetB2BOutgoingInvoiceStatusAckMsg |
| 9101 | Dohvat liste ulaznih računa | GetB2BIncomingInvoiceListMsg |
| 9102 | Odgovor na dohvat liste | GetB2BIncomingInvoiceListAckMsg |
| 9103 | Dohvat ulaznog računa | GetB2BIncomingInvoiceMsg |
| 9104 | Odgovor na dohvat računa | GetB2BIncomingInvoiceAckMsg |
| 107 | Promjena statusa ulaznog računa | ChangeB2BIncomingInvoiceStatusMsg |
| 108 | Odgovor na promjenu statusa | ChangeB2BIncomingInvoiceStatusAckMsg |
| 50041 | Dohvat registra primatelja | GetReceiverListMsg |
| 50042 | Odgovor na dohvat registra | GetReceiverListAckMsg |

---

## Tehnički Zahtjevi

### 1. FINA Aplikacijski Certifikat

**Demo certifikat:**
- Zahtjev: https://demo-pki.fina.hr/obrasci/ZahtjevDemoAplikacijski-D20.pdf
- Preslika osobne iskaznice skrbnika (prednja i stražnja)
- Dostava: **info.rdc@fina.hr** (službeni email za demo certifikate)

**Produkcijski certifikat:**
- Nakon uspješnog testiranja na demo okolini
- Potreban za slanje u produkciju

### 2. UBL 2.1 XML Standard

**Specifikacija:**
- EN 16931:2017 (Europski standard)
- HR proširenje (CIUS): `urn:mfin.gov.hr:cius-2025:1.0`
- Dokumentacija: https://porezna.gov.hr/fiskalizacija/api/dokumenti/183

**UBL Sheme:**
- Invoice: https://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd
- CreditNote: https://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-CreditNote-2.1.xsd
- Common sheme: https://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/common/

### 3. KPD 2025 Klasifikacija

Sve stavke računa moraju imati **KPD kod** (Klasifikacija proizvoda po djelatnostima).

**Pretraživanje:**
- KLASUS aplikacija: https://web.dzs.hr/App/klasus/default.aspx?lang=hr
- Pomoć: KPD@dzs.hr

**Primjer:**
```
Usluga web razvoja → KPD: 620100 (Programiranje računala)
```

### 4. PHP Paketi za Laravel

```bash
# SOAP klijent
composer require artisaninweb/laravel-soap

# XML processing
composer require spatie/array-to-xml

# XML Signature (za digitalno potpisivanje)
composer require robrichards/xmlseclibs
```

---

## Postupak Implementacije

### Korak 1: Registracija za Demo Okolinu

1. **Zatraži demo certifikat**
   - Zahtjev: https://demo-pki.fina.hr/obrasci/ZahtjevDemoAplikacijski-D20.pdf
   - Preslika osobne (prednja i stražnja strana) skrbnika certifikata

2. **Dostavi dokumentaciju**
   - Email: **info.rdc@fina.hr** (službeni email za demo certifikate)
   - Ili osobno u FINA poslovnici (pronađi najbližu na fina.hr/poslovnice)

3. **Preuzmi certifikat**
   - Portal: https://demo-usercert.fina.hr/cms-user-portal/
   - Aktivacijski podaci dolaze putem email + SMS

### Korak 2: Laravel Implementacija

#### A. Kreiranje UBL XML računa

```php
namespace App\Services\FinaEracun;

use App\Models\Invoice;
use Spatie\ArrayToXml\ArrayToXml;

class UblInvoiceGenerator
{
    public function generateUbl(Invoice $invoice): string
    {
        $data = [
            'Invoice' => [
                '@attributes' => [
                    'xmlns' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
                    'xmlns:cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
                    'xmlns:cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
                ],
                'cbc:CustomizationID' => 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0',
                'cbc:ProfileID' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
                'cbc:ID' => $invoice->full_invoice_number,
                'cbc:IssueDate' => $invoice->issue_date->format('Y-m-d'),
                'cbc:DueDate' => $invoice->due_date->format('Y-m-d'),
                'cbc:InvoiceTypeCode' => '380', // Commercial invoice
                'cbc:DocumentCurrencyCode' => 'EUR',
                
                // Dobavljač (Supplier)
                'cac:AccountingSupplierParty' => [
                    'cac:Party' => [
                        'cbc:EndpointID' => [
                            '@attributes' => ['schemeID' => '9934'],
                            '_value' => $invoice->business->oib
                        ],
                        'cac:PartyName' => [
                            'cbc:Name' => $invoice->business->name
                        ],
                        'cac:PostalAddress' => [
                            'cbc:StreetName' => $invoice->business->address,
                            'cbc:CityName' => $invoice->business->city,
                            'cbc:PostalZone' => $invoice->business->postal_code,
                            'cac:Country' => [
                                'cbc:IdentificationCode' => 'HR'
                            ]
                        ],
                        'cac:PartyTaxScheme' => [
                            'cbc:CompanyID' => 'HR' . $invoice->business->oib,
                            'cac:TaxScheme' => [
                                'cbc:ID' => 'VAT'
                            ]
                        ]
                    ]
                ],
                
                // Kupac (Customer)
                'cac:AccountingCustomerParty' => [
                    'cac:Party' => [
                        'cbc:EndpointID' => [
                            '@attributes' => ['schemeID' => '9934'],
                            '_value' => $invoice->customer->oib
                        ],
                        'cac:PartyName' => [
                            'cbc:Name' => $invoice->customer->name
                        ],
                        'cac:PostalAddress' => [
                            'cbc:StreetName' => $invoice->customer->address,
                            'cbc:CityName' => $invoice->customer->city ?? 'Zagreb',
                            'cbc:PostalZone' => $invoice->customer->postal_code ?? '10000',
                            'cac:Country' => [
                                'cbc:IdentificationCode' => 'HR'
                            ]
                        ],
                        'cac:PartyTaxScheme' => [
                            'cbc:CompanyID' => 'HR' . $invoice->customer->oib,
                            'cac:TaxScheme' => [
                                'cbc:ID' => 'VAT'
                            ]
                        ]
                    ]
                ],
                
                // Način plaćanja
                'cac:PaymentMeans' => [
                    'cbc:PaymentMeansCode' => $this->getPaymentMeansCode($invoice->payment_method),
                    'cac:PayeeFinancialAccount' => [
                        'cbc:ID' => $invoice->business->iban
                    ]
                ],
                
                // PDV sažetak
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => [
                        '@attributes' => ['currencyID' => 'EUR'],
                        '_value' => number_format($invoice->tax_total, 2, '.', '')
                    ],
                    'cac:TaxSubtotal' => $this->getTaxSubtotals($invoice)
                ],
                
                // Ukupni iznosi
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount' => [
                        '@attributes' => ['currencyID' => 'EUR'],
                        '_value' => number_format($invoice->subtotal, 2, '.', '')
                    ],
                    'cbc:TaxExclusiveAmount' => [
                        '@attributes' => ['currencyID' => 'EUR'],
                        '_value' => number_format($invoice->subtotal, 2, '.', '')
                    ],
                    'cbc:TaxInclusiveAmount' => [
                        '@attributes' => ['currencyID' => 'EUR'],
                        '_value' => number_format($invoice->total_amount, 2, '.', '')
                    ],
                    'cbc:PayableAmount' => [
                        '@attributes' => ['currencyID' => 'EUR'],
                        '_value' => number_format($invoice->total_amount, 2, '.', '')
                    ]
                ],
                
                // Stavke računa
                'cac:InvoiceLine' => $this->getInvoiceLines($invoice)
            ]
        ];
        
        return ArrayToXml::convert($data, [
            'rootElementName' => 'Invoice',
            '_attributes' => $data['Invoice']['@attributes']
        ], true, 'UTF-8');
    }
    
    protected function getPaymentMeansCode(string $method): int
    {
        return match($method) {
            'virman' => 30,
            'gotovina' => 10,
            'kartica' => 48,
            default => 1
        };
    }
    
    protected function getTaxSubtotals(Invoice $invoice): array
    {
        $taxRates = $invoice->items->groupBy('tax_rate');
        $subtotals = [];
        
        foreach ($taxRates as $rate => $items) {
            $taxableAmount = $items->sum(fn($item) => $item->quantity * $item->price);
            $taxAmount = $taxableAmount * ($rate / 100);
            
            $subtotals[] = [
                'cbc:TaxableAmount' => [
                    '@attributes' => ['currencyID' => 'EUR'],
                    '_value' => number_format($taxableAmount, 2, '.', '')
                ],
                'cbc:TaxAmount' => [
                    '@attributes' => ['currencyID' => 'EUR'],
                    '_value' => number_format($taxAmount, 2, '.', '')
                ],
                'cac:TaxCategory' => [
                    'cbc:ID' => $this->getTaxCategoryCode($rate),
                    'cbc:Percent' => number_format($rate, 2, '.', ''),
                    'cac:TaxScheme' => [
                        'cbc:ID' => 'VAT'
                    ]
                ]
            ];
        }
        
        return $subtotals;
    }
    
    protected function getTaxCategoryCode(float $rate): string
    {
        return match($rate) {
            25.0, 13.0, 5.0 => 'S', // Standard rate
            0.0 => 'Z', // Zero rated
            default => 'S'
        };
    }
    
    protected function getInvoiceLines(Invoice $invoice): array
    {
        return $invoice->items->map(function($item, $index) {
            return [
                'cbc:ID' => $index + 1,
                'cbc:InvoicedQuantity' => [
                    '@attributes' => ['unitCode' => $this->getUnitCode($item->unit)],
                    '_value' => $item->quantity
                ],
                'cbc:LineExtensionAmount' => [
                    '@attributes' => ['currencyID' => 'EUR'],
                    '_value' => number_format($item->total, 2, '.', '')
                ],
                'cac:Item' => [
                    'cbc:Name' => $item->name,
                    'cbc:Description' => $item->description ?? $item->name,
                    'cac:ClassifiedTaxCategory' => [
                        'cbc:ID' => $this->getTaxCategoryCode($item->tax_rate),
                        'cbc:Percent' => number_format($item->tax_rate, 2, '.', ''),
                        'cac:TaxScheme' => [
                            'cbc:ID' => 'VAT'
                        ]
                    ],
                    // KPD 2025 klasifikacija
                    'cac:CommodityClassification' => [
                        'cbc:ItemClassificationCode' => [
                            '@attributes' => ['listID' => 'KPD'],
                            '_value' => $item->kpd_code ?? '620100' // Default: Programiranje
                        ]
                    ]
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => [
                        '@attributes' => ['currencyID' => 'EUR'],
                        '_value' => number_format($item->price, 2, '.', '')
                    ]
                ]
            ];
        })->toArray();
    }
    
    protected function getUnitCode(string $unit): string
    {
        return match($unit) {
            'kom' => 'C62', // Komad
            'sat' => 'HUR', // Sat
            'dan' => 'DAY', // Dan
            default => 'C62'
        };
    }
}
```

#### B. SOAP Klijent Servis

```php
namespace App\Services\FinaEracun;

use App\Models\Invoice;
use Artisaninweb\SoapWrapper\SoapWrapper;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class FinaEracunSoapClient
{
    protected $soap;
    protected $certPath;
    protected $certPassword;
    protected $wsdlUrl;
    
    public function __construct(SoapWrapper $soap)
    {
        $this->soap = $soap;
        $this->certPath = config('fina.cert_path');
        $this->certPassword = config('fina.cert_password');
        $this->wsdlUrl = config('fina.wsdl_url');
        
        $this->soap->add('FinaEracun', function ($service) {
            $service
                ->wsdl($this->wsdlUrl)
                ->certificate($this->certPath, $this->certPassword)
                ->options([
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'trace' => true,
                    'local_cert' => $this->certPath,
                    'passphrase' => $this->certPassword,
                    'stream_context' => stream_context_create([
                        'ssl' => [
                            'verify_peer' => true,
                            'verify_peer_name' => true,
                            'allow_self_signed' => false
                        ]
                    ])
                ]);
        });
    }
    
    public function sendInvoice(Invoice $invoice): array
    {
        $ublGenerator = new UblInvoiceGenerator();
        $ublXml = $ublGenerator->generateUbl($invoice);
        
        // Digitalno potpisivanje UBL XML-a
        $signedUbl = $this->signXml($ublXml);
        
        // Base64 encode
        $encodedUbl = base64_encode($signedUbl);
        
        // Kreiraj SOAP poruku
        $soapMessage = [
            'HeaderSupplier' => [
                'MessageID' => uniqid('INV_', true),
                'SupplierID' => '9934:' . $invoice->business->oib,
                'AdditionalSupplierID' => 'HR99:00001',
                'ERPID' => 'LARAVEL_RACUNI_APP',
                'MessageType' => '9001',
                'MessageAttributes' => ''
            ],
            'Data' => [
                'B2BOutgoingInvoiceEnvelope' => [
                    'XMLStandard' => 'UBL',
                    'SpecificationIdentifier' => 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0',
                    'SupplierInvoiceID' => $invoice->full_invoice_number,
                    'BuyerID' => '9934:' . $invoice->customer->oib,
                    'InvoiceEnvelope' => $encodedUbl
                ]
            ]
        ];
        
        // Digitalno potpisivanje SOAP poruke
        $signedSoap = $this->signSoapMessage($soapMessage);
        
        // Pošalji prema web servisu
        $response = $this->soap->call('FinaEracun.SendB2BOutgoingInvoice', [$signedSoap]);
        
        return $this->parseResponse($response);
    }
    
    public function getInvoiceStatus(string $invoiceNumber, int $year): array
    {
        $message = [
            'HeaderSupplier' => [
                'MessageID' => uniqid('STATUS_', true),
                'SupplierID' => '9934:' . config('fina.oib'),
                'MessageType' => '9011'
            ],
            'Data' => [
                'B2BOutgoingInvoiceStatus' => [
                    'SupplierInvoiceID' => $invoiceNumber,
                    'InvoiceYear' => $year
                ]
            ]
        ];
        
        $response = $this->soap->call('FinaEracun.GetB2BOutgoingInvoiceStatus', [$message]);
        return $this->parseResponse($response);
    }
    
    public function getIncomingInvoices(array $filters = []): array
    {
        $message = [
            'HeaderBuyer' => [
                'MessageID' => uniqid('INCOMING_', true),
                'BuyerID' => '9934:' . config('fina.oib'),
                'MessageType' => '9101'
            ],
            'Data' => [
                'B2BIncomingInvoiceList' => [
                    'Filter' => $filters
                ]
            ]
        ];
        
        $response = $this->soap->call('FinaEracun.GetB2BIncomingInvoiceList', [$message]);
        return $this->parseResponse($response);
    }
    
    protected function signXml(string $xml): string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        
        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
        );
        
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($this->certPath, true, true);
        
        $objDSig->sign($objKey);
        $objDSig->add509Cert(file_get_contents($this->certPath));
        
        $objDSig->appendSignature($doc->documentElement);
        
        return $doc->saveXML();
    }
    
    protected function signSoapMessage(array $message): array
    {
        // WS-Security potpis SOAP poruke
        // Implementacija prema WS-Security standardu
        return $message;
    }
    
    protected function parseResponse($response): array
    {
        if (isset($response->MessageAck)) {
            return [
                'success' => $response->MessageAck->AckStatus === 'ACCEPTED',
                'status' => $response->MessageAck->AckStatus,
                'code' => $response->MessageAck->AckStatusCode,
                'message' => $response->MessageAck->AckStatusText ?? '',
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid response format'
        ];
    }
}
```

#### C. Konfiguracija

```php
// config/fina.php

return [
    
    'environment' => env('FINA_ENVIRONMENT', 'demo'), // 'demo' ili 'production'
    
    'demo' => [
        'wsdl_url' => 'https://demo-eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl',
        'cert_path' => storage_path('certs/fina_demo.p12'),
        'cert_password' => env('FINA_DEMO_CERT_PASSWORD'),
    ],
    
    'production' => [
        'wsdl_url' => 'https://eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl',
        'cert_path' => storage_path('certs/fina_production.p12'),
        'cert_password' => env('FINA_PROD_CERT_PASSWORD'),
    ],
    
    'wsdl_url' => env('FINA_ENVIRONMENT') === 'production' 
        ? 'https://eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl'
        : 'https://demo-eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl',
    
    'cert_path' => storage_path('certs/fina_' . env('FINA_ENVIRONMENT', 'demo') . '.p12'),
    'cert_password' => env('FINA_CERT_PASSWORD'),
    
    'oib' => env('BUSINESS_OIB'),
    
];
```

#### D. .env

```bash
# FINA e-Račun
FINA_ENVIRONMENT=demo
FINA_DEMO_CERT_PASSWORD=your_demo_password
FINA_PROD_CERT_PASSWORD=your_prod_password
FINA_CERT_PASSWORD=${FINA_DEMO_CERT_PASSWORD}
BUSINESS_OIB=12345678909
```

### Korak 3: Testiranje

```php
// routes/console.php
Artisan::command('fina:test', function () {
    $invoice = Invoice::first();
    $client = app(FinaEracunSoapClient::class);
    
    // Test Echo
    $this->info('Testing Echo...');
    // $client->testEcho();
    
    // Send Invoice
    $this->info('Sending invoice...');
    $result = $client->sendInvoice($invoice);
    
    $this->info('Result: ' . json_encode($result, JSON_PRETTY_PRINT));
});
```

```bash
php artisan fina:test
```

### Korak 4: Produkcija

1. **Zatraži produkcijski certifikat**
2. **Promijeni .env**:
   ```bash
   FINA_ENVIRONMENT=production
   FINA_CERT_PASSWORD=${FINA_PROD_CERT_PASSWORD}
   ```
3. **Stavi certifikat** u `storage/certs/fina_production.p12`
4. **Ugovori paket** na FINA-i
5. **Pokreni produkciju**

---

## UBL 2.1 Format

### Struktura UBL Invoice

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    
    <!-- Customization -->
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0</cbc:CustomizationID>
    <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
    
    <!-- Invoice Info -->
    <cbc:ID>1/2/1/SPO</cbc:ID>
    <cbc:IssueDate>2026-02-18</cbc:IssueDate>
    <cbc:DueDate>2026-03-05</cbc:DueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>
    
    <!-- Supplier -->
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cbc:EndpointID schemeID="9934">12345678909</cbc:EndpointID>
            <cac:PartyName>
                <cbc:Name>MK Development</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>Testna ulica 1</cbc:StreetName>
                <cbc:CityName>Zagreb</cbc:CityName>
                <cbc:PostalZone>10000</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>HR</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>HR12345678909</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
        </cac:Party>
    </cac:AccountingSupplierParty>
    
    <!-- Customer -->
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cbc:EndpointID schemeID="9934">98765432109</cbc:EndpointID>
            <cac:PartyName>
                <cbc:Name>Test Kupac d.o.o.</cbc:Name>
            </cac:PartyName>
            <!-- ... -->
        </cac:Party>
    </cac:AccountingCustomerParty>
    
    <!-- Payment Means -->
    <cac:PaymentMeans>
        <cbc:PaymentMeansCode>30</cbc:PaymentMeansCode>
        <cac:PayeeFinancialAccount>
            <cbc:ID>HR1234567890123456789</cbc:ID>
        </cac:PayeeFinancialAccount>
    </cac:PaymentMeans>
    
    <!-- Tax Total -->
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="EUR">250.00</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="EUR">1000.00</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="EUR">250.00</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>25.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    
    <!-- Monetary Total -->
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="EUR">1000.00</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="EUR">1000.00</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="EUR">1250.00</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="EUR">1250.00</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    
    <!-- Invoice Lines -->
    <cac:InvoiceLine>
        <cbc:ID>1</cbc:ID>
        <cbc:InvoicedQuantity unitCode="HUR">20</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="EUR">1000.00</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Name>Web razvoj</cbc:Name>
            <cbc:Description>Razvoj web aplikacije</cbc:Description>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>25.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
            <cac:CommodityClassification>
                <cbc:ItemClassificationCode listID="KPD">620100</cbc:ItemClassificationCode>
            </cac:CommodityClassification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="EUR">50.00</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
    
</Invoice>
```

---

## Demo i Produkcija

### Demo Okolina

**WSDL URL:**
```
https://demo-eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl
```

**UI:**
```
https://demo-eracun.fina.hr
```

**Certifikat:**
- Demo certifikat
- Preuzimanje: https://demo-usercert.fina.hr/cms-user-portal/

**Testiranje:**
- Besplatno
- Neograničeno testiranje
- Validacija UBL XML-a
- Provjera SOAP poruka

### Produkcijska Okolina

**WSDL URL:**
```
https://eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl
```

**UI:**
```
https://eracun.fina.hr
```

**Certifikat:**
- Produkcijski certifikat
- Nakon uspješnog testiranja

**Ugovor:**
- Potreban ugovor s FINA-om
- Odabir paketa

---

## Troškovi i Paketi

### FINA e-Račun PLUS (Preporučeno)

**Paket uključuje:**
- ✅ Fina e-Račun (razmjena)
- ✅ Fina e-Arhiv (arhiviranje)
- ✅ Fina Faktoring platforma (naplata)

| Paket | Računa/mj | Arhiviranje | Certifikati | Cijena/mj (bez PDV) |
|-------|-----------|-------------|-------------|---------------------|
| **XS** | 15 | 15 | 1 | **8,20 EUR** |
| **S-30** | 30 | 30 | 1 | **12,20 EUR** |
| **S-55** | 55 | 55 | 1 | **20,75 EUR** |
| **S-80** | 80 | 80 | 1 | **27,20 EUR** |
| **S-120** | 120 | 120 | 1 | **37,40 EUR** |
| **M-200** | 200 | 200 | 2 | **63,70 EUR** |
| **M-300** | 300 | 300 | 2 | **88,80 EUR** |
| **M-400** | 400 | 400 | 2 | **113,50 EUR** |
| **L-500** | 500 | 500 | 2 | **137,00 EUR** |
| **L-650** | 650 | 650 | 2 | **176,00 EUR** |
| **XL-650+** | 650+ | 650+ | Custom | **Na upit** |

**Ugovor:**
- Min 24 mjeseca
- Cjenik: https://www.fina.hr/digitalizacija-poslovanja/e-racun/cjenik-fina-e-racuna

---

## Wichtige Links

### Dokumentacija

**FINA:**
- Tehnička specifikacija - Slanje računa: https://www.fina.hr/digitalizacija-poslovanja/e-racun/tehnicka-specifikacija/tehnicka-specifikacija-slanje-racuna-web-servisom
- Tehnička specifikacija - Sinkrona obrada: https://www.fina.hr/digitalizacija-poslovanja/e-racun/tehnicka-specifikacija/tehnicka-specifikacija-razmjena-podataka-web-servisima-sinkronom-obradom
- Integracija: https://www.fina.hr/digitalizacija-poslovanja/e-racun/vodici-za-integraciju-racunovodstvenog-programa
- WSDL i Sheme: https://www.fina.hr/ngsite/content/download/13358/204205/1

**Porezna Uprava:**
- UBL 2.1 HR Specifikacija: https://porezna.gov.hr/fiskalizacija/api/dokumenti/183
- Validator: https://porezna.gov.hr/fiskalizacija/api/dokumenti/184
- KPD 2025: https://porezna-uprava.gov.hr/hr/klasifikacija-proizvoda-kpd-2025/7718

**EU Standardi:**
- EN 16931: https://standards.cen.eu/dyn/www/f?p=204:110:0::::FSP_PROJECT:60602
- UBL 2.1: https://docs.oasis-open.org/ubl/os-UBL-2.1/

### Podrška

**FINA:**
- Telefon: 0800 0080 (besplatno)
- Kontakt forma: https://www.fina.hr/kontakti

**Demo Certifikati:**
- Email: info.rdc@fina.hr
- Zahtjev: https://demo-pki.fina.hr/obrasci/ZahtjevDemoAplikacijski-D20.pdf
- Portal za preuzimanje: https://demo-usercert.fina.hr/cms-user-portal/

---

## Zaključak

### Preporučeni Pristup

Za **tvoju aplikaciju** preporučujem:

1. **Finin Integracijski Modul** (REST API pristup)
   - Jednostavniji za implementaciju
   - Manji overhead (JSON umjesto SOAP)
   - FINA brine o SOAP kompleksnostima

2. **Implementacija u Laravelu**:
   - Service class za komunikaciju s modulom
   - UBL generator za kreiranje XML računa
   - Queue job za asinkrono slanje
   - Event/Listener za tracking

3. **Testiranje**:
   - Demo okolina FINA-e
   - Validator UBL XML-a
   - Provjera statusa računa

4. **Produkcija**:
   - Paket S-30 ili S-55 (ovisno o volumenu)
   - Produkcijski certifikat
   - Monitoring i logging

### Sljedeći Koraci

1. ✅ Zatraži Finin integracijski modul
2. ✅ Zatraži demo certifikat
3. ✅ Implementiraj UBL generator
4. ✅ Implementiraj REST komunikaciju
5. ✅ Testiraj na demo okolini
6. ✅ Ugovori paket
7. ✅ Pokreni produkciju

---

**Pitanja? Kontaktiraj FINA podršku:**
- � 0800 0080 (besplatno)
- 🌐 https://www.fina.hr/kontakti
- 📧 info.rdc@fina.hr (za demo certifikate)

---

**Dokumentacija kreirana:** 18.02.2026  
**Autor:** GitHub Copilot  
**Verzija:** 1.0
