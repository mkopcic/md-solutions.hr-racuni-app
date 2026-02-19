<?php

namespace Database\Factories;

use App\Enums\EracunStatus;
use App\Enums\FinaStatus;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EracunLog>
 */
class EracunLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sentAt = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'invoice_id' => Invoice::factory(),
            'direction' => 'outgoing',
            'message_id' => 'MSG-'.fake()->uuid(),
            'fina_invoice_id' => 'FI-'.fake()->uuid(),
            'ubl_xml' => $this->generateDummyUblXml(),
            'request_xml' => $this->generateDummyRequestXml(),
            'response_xml' => $this->generateDummyResponseXml(),
            'status' => EracunStatus::SENT,
            'fina_status' => FinaStatus::RECEIVING_CONFIRMED,
            'error_message' => null,
            'retry_count' => 0,
            'sent_at' => $sentAt,
            'retried_at' => null,
            'status_checked_at' => fake()->dateTimeBetween($sentAt, 'now'),
        ];
    }

    /**
     * Status: Pending (čeka slanje)
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EracunStatus::PENDING,
            'fina_status' => null,
            'sent_at' => null,
            'status_checked_at' => null,
            'response_xml' => null,
        ]);
    }

    /**
     * Status: Sent (poslan)
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EracunStatus::SENT,
            'fina_status' => FinaStatus::RECEIVED,
        ]);
    }

    /**
     * Status: Delivered/Confirmed (dostavljen i potvrđen)
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EracunStatus::SENT,
            'fina_status' => FinaStatus::RECEIVING_CONFIRMED,
        ]);
    }

    /**
     * Status: Failed (neuspješno)
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EracunStatus::FAILED,
            'fina_status' => null,
            'error_message' => fake()->randomElement([
                'Neispravan OIB kupca',
                'Neispravan format XML-a',
                'Digitalni potpis nije valjan',
                'Certifikat je istekao',
                'Kupac nije registriran u e-Račun sustavu',
            ]),
        ]);
    }

    /**
     * Status: Accepted (prihvaćen od FINA)
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EracunStatus::ACCEPTED,
            'fina_status' => FinaStatus::APPROVED,
        ]);
    }

    /**
     * Status: Rejected (odbijen od FINA)
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EracunStatus::REJECTED,
            'fina_status' => FinaStatus::REJECTED,
            'error_message' => fake()->randomElement([
                'Neispravan format računa',
                'Nedostaju obavezna polja',
                'Nevaljan digitalni potpis',
            ]),
        ]);
    }

    /**
     * Generiraj dummy UBL XML
     */
    protected function generateDummyUblXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
    <cbc:ID>'.fake()->numerify('####-##-##').'</cbc:ID>
    <cbc:IssueDate>'.now()->format('Y-m-d').'</cbc:IssueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>
</Invoice>';
    }

    /**
     * Generiraj dummy Request XML (SOAP envelope)
     */
    protected function generateDummyRequestXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Header>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:BinarySecurityToken>DUMMY_TOKEN</wsse:BinarySecurityToken>
        </wsse:Security>
    </soap:Header>
    <soap:Body>
        <SendB2BOutgoingInvoiceMsg>
            <HeaderSupplier>
                <MessageID>MSG-'.fake()->uuid().'</MessageID>
                <SupplierID>9934:12345678909</SupplierID>
            </HeaderSupplier>
        </SendB2BOutgoingInvoiceMsg>
    </soap:Body>
</soap:Envelope>';
    }

    /**
     * Generiraj dummy Response XML
     */
    protected function generateDummyResponseXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <SendB2BOutgoingInvoiceAckMsg>
            <ResponseCode>0000</ResponseCode>
            <ResponseMessage>Račun je uspješno zaprimljen</ResponseMessage>
            <InvoiceID>FI-'.fake()->uuid().'</InvoiceID>
        </SendB2BOutgoingInvoiceAckMsg>
    </soap:Body>
</soap:Envelope>';
    }
}
