<?php

namespace App\Services\EracunFina;

use App\Models\Invoice;
use DOMDocument;
use DOMException;

/**
 * Generira UBL 2.1 XML format za e-Račun prema EN 16931 standardu
 */
class UblInvoiceGenerator
{
    protected EracunContext $context;

    public function __construct(EracunContext $context)
    {
        $this->context = $context;
    }

    /**
     * Generira UBL 2.1 XML iz Invoice modela
     */
    public function generate(Invoice $invoice): string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Root element - Invoice
        $root = $xml->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->appendChild($root);

        // CustomizationID - HR specifikacija
        $this->addElement($xml, $root, 'cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0');

        // ProfileID - Peppol
        $this->addElement($xml, $root, 'cbc:ProfileID', 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0');

        // ID - Broj računa
        $this->addElement($xml, $root, 'cbc:ID', $invoice->full_invoice_number);

        // IssueDate
        $this->addElement($xml, $root, 'cbc:IssueDate', $invoice->issue_date->format('Y-m-d'));

        // DueDate
        $this->addElement($xml, $root, 'cbc:DueDate', $invoice->due_date->format('Y-m-d'));

        // InvoiceTypeCode - 380 = Commercial invoice
        $this->addElement($xml, $root, 'cbc:InvoiceTypeCode', '380');

        // DocumentCurrencyCode
        $this->addElement($xml, $root, 'cbc:DocumentCurrencyCode', 'EUR');

        // Note (optional)
        if ($invoice->notes) {
            $this->addElement($xml, $root, 'cbc:Note', $invoice->notes);
        }

        // AccountingSupplierParty (Dobavljač)
        $this->addSupplierParty($xml, $root, $invoice);

        // AccountingCustomerParty (Kupac)
        $this->addCustomerParty($xml, $root, $invoice);

        // PaymentMeans (Način plaćanja)
        $this->addPaymentMeans($xml, $root, $invoice);

        // TaxTotal (PDV sažetak)
        $this->addTaxTotal($xml, $root, $invoice);

        // LegalMonetaryTotal (Ukupni iznosi)
        $this->addLegalMonetaryTotal($xml, $root, $invoice);

        // InvoiceLines (Stavke računa)
        $this->addInvoiceLines($xml, $root, $invoice);

        return $xml->saveXML();
    }

    protected function addSupplierParty(DOMDocument $xml, $root, Invoice $invoice): void
    {
        $supplierParty = $xml->createElement('cac:AccountingSupplierParty');
        $party = $xml->createElement('cac:Party');

        // EndpointID - OIB sa šifrom 9934
        $endpoint = $xml->createElement('cbc:EndpointID', $this->context->supplierOib);
        $endpoint->setAttribute('schemeID', '9934');
        $party->appendChild($endpoint);

        // PartyName
        $partyName = $xml->createElement('cac:PartyName');
        $this->addElement($xml, $partyName, 'cbc:Name', $this->context->supplierName);
        $party->appendChild($partyName);

        // PostalAddress
        $postalAddress = $xml->createElement('cac:PostalAddress');
        $this->addElement($xml, $postalAddress, 'cbc:StreetName', $this->context->supplierAddress);
        $this->addElement($xml, $postalAddress, 'cbc:CityName', $this->context->supplierCity);
        $this->addElement($xml, $postalAddress, 'cbc:PostalZone', $this->context->supplierPostalCode);
        $country = $xml->createElement('cac:Country');
        $this->addElement($xml, $country, 'cbc:IdentificationCode', 'HR');
        $postalAddress->appendChild($country);
        $party->appendChild($postalAddress);

        // PartyTaxScheme
        $partyTaxScheme = $xml->createElement('cac:PartyTaxScheme');
        $this->addElement($xml, $partyTaxScheme, 'cbc:CompanyID', 'HR' . $this->context->supplierOib);
        $taxScheme = $xml->createElement('cac:TaxScheme');
        $this->addElement($xml, $taxScheme, 'cbc:ID', 'VAT');
        $partyTaxScheme->appendChild($taxScheme);
        $party->appendChild($partyTaxScheme);

        $supplierParty->appendChild($party);
        $root->appendChild($supplierParty);
    }

    protected function addCustomerParty(DOMDocument $xml, $root, Invoice $invoice): void
    {
        $customerParty = $xml->createElement('cac:AccountingCustomerParty');
        $party = $xml->createElement('cac:Party');

        // EndpointID - OIB kupca
        $endpoint = $xml->createElement('cbc:EndpointID', $invoice->customer->oib);
        $endpoint->setAttribute('schemeID', '9934');
        $party->appendChild($endpoint);

        // PartyName
        $partyName = $xml->createElement('cac:PartyName');
        $this->addElement($xml, $partyName, 'cbc:Name', $invoice->customer->name);
        $party->appendChild($partyName);

        // PostalAddress
        $postalAddress = $xml->createElement('cac:PostalAddress');
        $this->addElement($xml, $postalAddress, 'cbc:StreetName', $invoice->customer->address ?? 'Nepoznata adresa');
        $this->addElement($xml, $postalAddress, 'cbc:CityName', $invoice->customer->city ?? 'Zagreb');
        $this->addElement($xml, $postalAddress, 'cbc:PostalZone', $invoice->customer->postal_code ?? '10000');
        $country = $xml->createElement('cac:Country');
        $this->addElement($xml, $country, 'cbc:IdentificationCode', 'HR');
        $postalAddress->appendChild($country);
        $party->appendChild($postalAddress);

        // PartyTaxScheme
        $partyTaxScheme = $xml->createElement('cac:PartyTaxScheme');
        $this->addElement($xml, $partyTaxScheme, 'cbc:CompanyID', 'HR' . $invoice->customer->oib);
        $taxScheme = $xml->createElement('cac:TaxScheme');
        $this->addElement($xml, $taxScheme, 'cbc:ID', 'VAT');
        $partyTaxScheme->appendChild($taxScheme);
        $party->appendChild($partyTaxScheme);

        $customerParty->appendChild($party);
        $root->appendChild($customerParty);
    }

    protected function addPaymentMeans(DOMDocument $xml, $root, Invoice $invoice): void
    {
        $paymentMeans = $xml->createElement('cac:PaymentMeans');

        // PaymentMeansCode - 30 = Virman, 10 = Gotovina
        $paymentCode = match($invoice->payment_method ?? 'virman') {
            'gotovina' => '10',
            'kartica' => '48',
            'virman', 'transakcija' => '30',
            default => '30'
        };
        $this->addElement($xml, $paymentMeans, 'cbc:PaymentMeansCode', $paymentCode);

        // PayeeFinancialAccount (IBAN)
        $financialAccount = $xml->createElement('cac:PayeeFinancialAccount');
        $this->addElement($xml, $financialAccount, 'cbc:ID', $this->context->supplierIban);
        $paymentMeans->appendChild($financialAccount);

        $root->appendChild($paymentMeans);
    }

    protected function addTaxTotal(DOMDocument $xml, $root, Invoice $invoice): void
    {
        $taxTotal = $xml->createElement('cac:TaxTotal');

        // TaxAmount - Ukupan iznos PDV-a
        $taxAmount = $xml->createElement('cbc:TaxAmount', number_format($invoice->tax_total ?? 0, 2, '.', ''));
        $taxAmount->setAttribute('currencyID', 'EUR');
        $taxTotal->appendChild($taxAmount);

        // TaxSubtotal - Raščlamba po stopama PDV-a
        $taxRates = $invoice->items->groupBy('tax_rate');

        foreach ($taxRates as $rate => $items) {
            $taxableAmount = $items->sum(fn ($item) => $item->quantity * $item->price);
            $taxAmountForRate = $taxableAmount * ($rate / 100);

            $taxSubtotal = $xml->createElement('cac:TaxSubtotal');

            $taxableAmountEl = $xml->createElement('cbc:TaxableAmount', number_format($taxableAmount, 2, '.', ''));
            $taxableAmountEl->setAttribute('currencyID', 'EUR');
            $taxSubtotal->appendChild($taxableAmountEl);

            $taxAmountEl = $xml->createElement('cbc:TaxAmount', number_format($taxAmountForRate, 2, '.', ''));
            $taxAmountEl->setAttribute('currencyID', 'EUR');
            $taxSubtotal->appendChild($taxAmountEl);

            $taxCategory = $xml->createElement('cac:TaxCategory');
            $this->addElement($xml, $taxCategory, 'cbc:ID', $this->getTaxCategoryCode($rate));
            $this->addElement($xml, $taxCategory, 'cbc:Percent', number_format($rate, 2, '.', ''));
            $taxScheme = $xml->createElement('cac:TaxScheme');
            $this->addElement($xml, $taxScheme, 'cbc:ID', 'VAT');
            $taxCategory->appendChild($taxScheme);
            $taxSubtotal->appendChild($taxCategory);

            $taxTotal->appendChild($taxSubtotal);
        }

        $root->appendChild($taxTotal);
    }

    protected function addLegalMonetaryTotal(DOMDocument $xml, $root, Invoice $invoice): void
    {
        $monetaryTotal = $xml->createElement('cac:LegalMonetaryTotal');

        // LineExtensionAmount - Ukupno bez PDV-a
        $lineExtension = $xml->createElement('cbc:LineExtensionAmount', number_format($invoice->subtotal ?? 0, 2, '.', ''));
        $lineExtension->setAttribute('currencyID', 'EUR');
        $monetaryTotal->appendChild($lineExtension);

        // TaxExclusiveAmount - Ukupno bez PDV-a
        $taxExclusive = $xml->createElement('cbc:TaxExclusiveAmount', number_format($invoice->subtotal ?? 0, 2, '.', ''));
        $taxExclusive->setAttribute('currencyID', 'EUR');
        $monetaryTotal->appendChild($taxExclusive);

        // TaxInclusiveAmount - Ukupno sa PDV-om
        $taxInclusive = $xml->createElement('cbc:TaxInclusiveAmount', number_format($invoice->total_amount ?? 0, 2, '.', ''));
        $taxInclusive->setAttribute('currencyID', 'EUR');
        $monetaryTotal->appendChild($taxInclusive);

        // PayableAmount - Iznos za plaćanje
        $payable = $xml->createElement('cbc:PayableAmount', number_format($invoice->total_amount ?? 0, 2, '.', ''));
        $payable->setAttribute('currencyID', 'EUR');
        $monetaryTotal->appendChild($payable);

        $root->appendChild($monetaryTotal);
    }

    protected function addInvoiceLines(DOMDocument $xml, $root, Invoice $invoice): void
    {
        foreach ($invoice->items as $index => $item) {
            $invoiceLine = $xml->createElement('cac:InvoiceLine');

            // ID - Redni broj stavke
            $this->addElement($xml, $invoiceLine, 'cbc:ID', (string)($index + 1));

            // InvoicedQuantity
            $quantity = $xml->createElement('cbc:InvoicedQuantity', (string)$item->quantity);
            $quantity->setAttribute('unitCode', $this->getUnitCode($item->unit ?? 'kom'));
            $invoiceLine->appendChild($quantity);

            // LineExtensionAmount
            $lineExtension = $xml->createElement('cbc:LineExtensionAmount', number_format($item->total ?? 0, 2, '.', ''));
            $lineExtension->setAttribute('currencyID', 'EUR');
            $invoiceLine->appendChild($lineExtension);

            // Item
            $itemEl = $xml->createElement('cac:Item');
            $this->addElement($xml, $itemEl, 'cbc:Name', $item->name);

            if ($item->description) {
                $this->addElement($xml, $itemEl, 'cbc:Description', $item->description);
            }

            // ClassifiedTaxCategory
            $taxCategory = $xml->createElement('cac:ClassifiedTaxCategory');
            $this->addElement($xml, $taxCategory, 'cbc:ID', $this->getTaxCategoryCode($item->tax_rate ?? 25));
            $this->addElement($xml, $taxCategory, 'cbc:Percent', number_format($item->tax_rate ?? 25, 2, '.', ''));
            $taxScheme = $xml->createElement('cac:TaxScheme');
            $this->addElement($xml, $taxScheme, 'cbc:ID', 'VAT');
            $taxCategory->appendChild($taxScheme);
            $itemEl->appendChild($taxCategory);

            // CommodityClassification - KPD 2025 kod
            $commodityClassification = $xml->createElement('cac:CommodityClassification');
            $kpdCode = $xml->createElement('cbc:ItemClassificationCode', $item->kpd_code ?? '620100');
            $kpdCode->setAttribute('listID', 'KPD');
            $commodityClassification->appendChild($kpdCode);
            $itemEl->appendChild($commodityClassification);

            $invoiceLine->appendChild($itemEl);

            // Price
            $priceEl = $xml->createElement('cac:Price');
            $priceAmount = $xml->createElement('cbc:PriceAmount', number_format($item->price ?? 0, 2, '.', ''));
            $priceAmount->setAttribute('currencyID', 'EUR');
            $priceEl->appendChild($priceAmount);
            $invoiceLine->appendChild($priceEl);

            $root->appendChild($invoiceLine);
        }
    }

    protected function getTaxCategoryCode(float $rate): string
    {
        return match($rate) {
            25.0, 13.0, 5.0 => 'S', // Standard rate
            0.0 => 'Z', // Zero rated goods
            default => 'S'
        };
    }

    protected function getUnitCode(string $unit): string
    {
        return match($unit) {
            'kom' => 'C62',  // Piece
            'sat' => 'HUR',  // Hour
            'dan' => 'DAY',  // Day
            'kg' => 'KGM',   // Kilogram
            'm2' => 'MTK',   // Square meter
            default => 'C62'
        };
    }

    protected function addElement(DOMDocument $xml, $parent, string $name, string $value): void
    {
        $element = $xml->createElement($name, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
        $parent->appendChild($element);
    }
}
