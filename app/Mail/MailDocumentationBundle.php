<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailDocumentationBundle extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📧 Mail Server Dokumentacija - md-solutions.hr',
            replyTo: 'md-solutions@md-solutions.hr',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mail-documentation-bundle',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath('/home/md-solutions/MAIL-SERVER-STATUS.md')
                ->as('MAIL-SERVER-STATUS.md')
                ->withMime('text/markdown'),
            
            Attachment::fromPath('/home/md-solutions/SPAM-ANALIZA.md')
                ->as('SPAM-ANALIZA.md')
                ->withMime('text/markdown'),
            
            Attachment::fromPath('/home/md-solutions/MAIL-SETUP-ZAVRSENO.md')
                ->as('MAIL-SETUP-ZAVRSENO.md')
                ->withMime('text/markdown'),
        ];
    }
}
