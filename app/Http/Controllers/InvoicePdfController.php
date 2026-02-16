<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoicePdfController extends Controller
{
    /**
     * Generate HUB3 QR code for Croatian payment
     */
    protected function generateQrCode(Invoice $invoice, Business $business): string
    {
        $amount = number_format($invoice->total_amount, 2, '.', '');
        $invoiceNumber = $invoice->full_invoice_number ?? $invoice->id;

        // HUB3 standard format
        $hub3Data = "HRVHUB30\n";
        $hub3Data .= "EUR{$amount}\n";
        $hub3Data .= "{$business->name}\n";
        $hub3Data .= "{$business->address}\n";
        $hub3Data .= "{$business->location}\n";
        $hub3Data .= "{$business->iban}\n";
        $hub3Data .= "HR00\n";
        $hub3Data .= "{$invoiceNumber}\n";
        $hub3Data .= "GDSV\n";
        $hub3Data .= "Racun {$invoiceNumber}";

        // Generate QR code as SVG base64 data URI
        $svg = QrCode::size(150)->generate($hub3Data);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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
