<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Le\PaymentBarcodeGenerator\Data;
use Le\PaymentBarcodeGenerator\Generator;
use Le\PaymentBarcodeGenerator\Party;
use Le\PDF417\PDF417;
use Le\PDF417\Renderer\ImageRenderer;

class InvoicePdfController extends Controller
{
    /**
     * Generate HUB3 PDF417 barcode for Croatian payment
     */
    protected function generateQrCode(Invoice $invoice, Business $business): string
    {
        // Amount in cents (HUB3 requires integer in smallest currency unit)
        $amountInCents = (int) round($invoice->total_amount * 100);
        $invoiceNumber = $invoice->full_invoice_number ?? $invoice->id;

        // Payer can be empty (customer pays)
        $payer = new Party('', '', '');

        // Payee is the business
        $payee = new Party(
            $business->name,
            $business->address,
            $business->location
        );

        // Create HUB3 data
        $data = new Data(
            payer: $payer,
            payee: $payee,
            iban: $business->iban,
            currency: 'EUR',
            amount: $amountInCents,
            model: 'HR01',
            reference: (string) $invoiceNumber,
            code: 'COST',
            description: "Racun br. {$invoiceNumber}"
        );

        // Generate PDF417 barcode as PNG (better quality for scanning)
        $pdf417 = new PDF417;
        $pdf417->setSecurityLevel(4);
        $pdf417->setColumns(9);

        $renderer = new ImageRenderer([
            'format' => 'data-url',
            'scale' => 4,      // Povećano sa 2 na 4 za bolju kvalitetu
            'ratio' => 3,
            'padding' => 30,   // Povećano padding za čiste rubove
        ]);

        $generator = new Generator($pdf417, $renderer);
        $imageData = $generator->render($data);

        // data-url format returns an Image object encoded as data URL
        return (string) $imageData;
    }

    public function viewPdf(Invoice $invoice)
    {
        try {
            \Log::info('PDF generation started', [
                'invoice_id' => $invoice->id,
                'customer' => $invoice->customer->name ?? 'N/A',
            ]);

            $business = Business::first();

            if (! $business) {
                throw new \Exception('Podaci o poslovanju nisu konfigurirani');
            }

            $qrCode = $this->generateQrCode($invoice, $business);

            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'business' => $business,
                'qrCode' => $qrCode,
            ])->setPaper('a4');

            \Log::info('PDF generated successfully', [
                'invoice_id' => $invoice->id,
            ]);

            return $pdf->stream('racun-'.$invoice->id.'.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id ?? null,
            ]);

            return response()->json([
                'error' => 'Greška pri generiranju PDF-a: '.$e->getMessage(),
            ], 500);
        }
    }
}
