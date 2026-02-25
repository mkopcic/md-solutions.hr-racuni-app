<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePdfMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public Business $business
    ) {}

    /**
     * Get the queueable relationships for the invoice model.
     */
    public function getQueueableRelations(): array
    {
        return ['customer', 'items'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Račun #{$this->invoice->full_invoice_number} - {$this->business->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-pdf',
            with: [
                'invoice' => $this->invoice,
                'business' => $this->business,
                'customerName' => $this->invoice->customer->name,
                'totalAmount' => number_format($this->invoice->total_amount, 2, ',', '.'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generiraj PDF sa QR kodom
        $qrCode = $this->generateQrCode();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'business' => $this->business,
            'qrCode' => $qrCode,
        ])->setPaper('a4');

        return [
            Attachment::fromData(fn () => $pdf->output(), "racun-{$this->invoice->full_invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Generate QR code for payment
     */
    protected function generateQrCode(): string
    {
        // Amount in cents (HUB3 requires integer in smallest currency unit)
        $amountInCents = (int) round($this->invoice->total_amount * 100);
        $invoiceNumber = $this->invoice->full_invoice_number ?? $this->invoice->id;

        // Payer can be empty (customer pays)
        $payer = new \Le\PaymentBarcodeGenerator\Party('', '', '');

        // Payee is the business
        $payee = new \Le\PaymentBarcodeGenerator\Party(
            $this->business->name,
            $this->business->address,
            $this->business->location
        );

        // Create HUB3 data
        $data = new \Le\PaymentBarcodeGenerator\Data(
            payer: $payer,
            payee: $payee,
            iban: $this->business->iban,
            currency: 'EUR',
            amount: $amountInCents,
            model: 'HR01',
            reference: (string) $invoiceNumber,
            code: 'COST',
            description: "Racun br. {$invoiceNumber}"
        );

        // Generate PDF417 barcode as PNG
        $pdf417 = new \Le\PDF417\PDF417;
        $pdf417->setSecurityLevel(4);
        $pdf417->setColumns(9);

        $renderer = new \Le\PDF417\Renderer\ImageRenderer([
            'format' => 'data-url',
            'scale' => 4,
            'ratio' => 3,
            'padding' => 30,
        ]);

        $generator = new \Le\PaymentBarcodeGenerator\Generator($pdf417, $renderer);
        $imageData = $generator->render($data);

        // data-url format returns an Image object encoded as data URL
        return (string) $imageData;
    }
}
