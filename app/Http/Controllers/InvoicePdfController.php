<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Business;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function view(Invoice $invoice)
    {
        return $this->viewPdf($invoice);
    }

    public function viewPdf(Invoice $invoice)
    {
        try {
            \Log::info('PDF generation started', [
                'invoice_id' => $invoice->id,
                'customer_name' => $invoice->customer->name
            ]);

            // Get business data
            $business = Business::first();

            if (!$business) {
                throw new \Exception('Podaci o poslovanju nisu konfigurirani');
            }

            // Clean UTF-8 data before sending to PDF
            $cleanInvoice = $invoice;
            $cleanBusiness = $business;

            // Convert problematic characters
            if ($cleanInvoice->customer) {
                $cleanInvoice->customer->name = mb_convert_encoding($cleanInvoice->customer->name, 'UTF-8', 'UTF-8');
                $cleanInvoice->customer->address = mb_convert_encoding($cleanInvoice->customer->address, 'UTF-8', 'UTF-8');
                $cleanInvoice->customer->city = mb_convert_encoding($cleanInvoice->customer->city, 'UTF-8', 'UTF-8');
            }

            $cleanBusiness->name = mb_convert_encoding($cleanBusiness->name, 'UTF-8', 'UTF-8');
            $cleanBusiness->address = mb_convert_encoding($cleanBusiness->address, 'UTF-8', 'UTF-8');

            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $cleanInvoice,
                'business' => $cleanBusiness
            ])->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true
            ]);

            \Log::info('PDF generated successfully');

            return $pdf->stream('racun-' . $invoice->id . '.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Greška pri generiranju PDF-a: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(Invoice $invoice)
    {
        try {
            \Log::info('PDF download started', [
                'invoice_id' => $invoice->id,
                'customer_name' => $invoice->customer->name
            ]);

            // Get business data
            $business = Business::first();

            if (!$business) {
                throw new \Exception('Podaci o poslovanju nisu konfigurirani');
            }

            // Clean UTF-8 data before sending to PDF
            $cleanInvoice = $invoice;
            $cleanBusiness = $business;

            // Convert problematic characters
            if ($cleanInvoice->customer) {
                $cleanInvoice->customer->name = mb_convert_encoding($cleanInvoice->customer->name, 'UTF-8', 'UTF-8');
                $cleanInvoice->customer->address = mb_convert_encoding($cleanInvoice->customer->address, 'UTF-8', 'UTF-8');
                $cleanInvoice->customer->city = mb_convert_encoding($cleanInvoice->customer->city, 'UTF-8', 'UTF-8');
            }

            $cleanBusiness->name = mb_convert_encoding($cleanBusiness->name, 'UTF-8', 'UTF-8');
            $cleanBusiness->address = mb_convert_encoding($cleanBusiness->address, 'UTF-8', 'UTF-8');

            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $cleanInvoice,
                'business' => $cleanBusiness
            ])->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true
            ]);

            \Log::info('PDF download generated successfully');

            return $pdf->download('racun-' . $invoice->id . '.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF download failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Greška pri generiranju PDF-a: ' . $e->getMessage()
            ], 500);
        }
    }
}
