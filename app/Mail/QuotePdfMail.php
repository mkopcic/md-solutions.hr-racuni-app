<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotePdfMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Quote $quote,
        public Business $business
    ) {}

    /**
     * Get the queueable relationships for the quote model.
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
            subject: "Ponuda #{$this->quote->full_quote_number} - {$this->business->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-pdf',
            with: [
                'quote' => $this->quote,
                'business' => $this->business,
                'customerName' => $this->quote->customer->name,
                'totalAmount' => number_format($this->quote->total_amount, 2, ',', '.'),
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
        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $this->quote,
            'business' => $this->business,
        ])->setPaper('a4');

        return [
            Attachment::fromData(fn () => $pdf->output(), "ponuda-{$this->quote->full_quote_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
