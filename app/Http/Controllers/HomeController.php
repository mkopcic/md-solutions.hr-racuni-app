<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use Le\PaymentBarcodeGenerator\Data;
use Le\PaymentBarcodeGenerator\Generator;
use Le\PaymentBarcodeGenerator\Party;
use Le\PDF417\PDF417;
use Le\PDF417\Renderer\ImageRenderer;

class HomeController extends Controller
{
    /**
     * Generate and display test barcode for invoice
     */
    public function testBarcode(Invoice $invoice)
    {
        $business = Business::first();
        $amountInCents = (int) round($invoice->total_amount * 100);
        $invoiceNumber = $invoice->full_invoice_number ?? $invoice->id;

        $payer = new Party('', '', '');
        $payee = new Party(
            $business->name,
            $business->address,
            $business->location
        );

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

        $pdf417 = new PDF417;
        $pdf417->setSecurityLevel(4);
        $pdf417->setColumns(9);

        $renderer = new ImageRenderer([
            'format' => 'png',
            'scale' => 4,
            'ratio' => 3,
            'padding' => 30,
        ]);

        $generator = new Generator($pdf417, $renderer);
        $image = $generator->render($data);

        return response($image)->header('Content-Type', 'image/png');
    }
}
