<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Le\PaymentBarcodeGenerator\Data;
use Le\PaymentBarcodeGenerator\Generator;
use Le\PaymentBarcodeGenerator\Party;
use Le\PDF417\PDF417;
use Le\PDF417\Renderer\ImageRenderer;

class QuotePdfController extends Controller
{
    /**
     * Generate HUB3 PDF417 barcode for Croatian payment
     */
    protected function generateQrCode(Quote $quote, Business $business): string
    {
        // Amount in cents (HUB3 requires integer in smallest currency unit)
        $amountInCents = (int) round($quote->total_amount * 100);
        $quoteNumber = $quote->full_quote_number ?? $quote->id;

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
            reference: (string) $quoteNumber,
            code: 'COST',
            description: "Ponuda br. {$quoteNumber}"
        );

        // Generate PDF417 barcode as PNG (better quality for scanning)
        $pdf417 = new PDF417;
        $pdf417->setSecurityLevel(4);
        $pdf417->setColumns(9);

        $renderer = new ImageRenderer([
            'format' => 'data-url',
            'scale' => 4,
            'ratio' => 3,
            'padding' => 30,
        ]);

        $generator = new Generator($pdf417, $renderer);
        $imageData = $generator->render($data);

        // data-url format returns an Image object encoded as data URL
        return (string) $imageData;
    }

    public function viewPdf(Quote $quote)
    {
        try {
            \Log::info('PDF generation started', [
                'quote_id' => $quote->id,
                'customer' => $quote->customer->name ?? 'N/A',
            ]);

            $business = Business::first();

            if (! $business) {
                throw new \Exception('Podaci o poslovanju nisu konfigurirani');
            }

            $qrCode = $this->generateQrCode($quote, $business);

            $pdf = Pdf::loadView('pdf.quote', [
                'quote' => $quote,
                'business' => $business,
                'qrCode' => $qrCode,
            ])->setPaper('a4');

            \Log::info('PDF generated successfully', [
                'quote_id' => $quote->id,
            ]);

            return $pdf->stream('ponuda-'.$quote->id.'.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'quote_id' => $quote->id ?? null,
            ]);

            return response()->json(['error' => 'Greška pri generiranju PDF-a'], 500);
        }
    }
}
