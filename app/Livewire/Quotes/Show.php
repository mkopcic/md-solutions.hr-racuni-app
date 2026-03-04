<?php

namespace App\Livewire\Quotes;

use App\Models\Business;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class Show extends Component
{
    public Quote $quote;

    public $business;

    public function mount(Quote $quote)
    {
        $this->quote = $quote->load(['customer', 'items', 'convertedToInvoice']);
        $this->business = Business::first();

        if (! $this->quote->exists) {
            abort(404, 'Ponuda nije pronađena');
        }
    }

    public function render()
    {
        return view('livewire.quotes.show')
            ->layout('components.layouts.app', ['title' => 'Ponuda #'.$this->quote->full_quote_number]);
    }

    public function generatePdf()
    {
        // Kreiraj direktorij ako ne postoji
        $directory = storage_path('app/public/quotes');
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $this->quote,
            'business' => $this->business,
        ]);

        $filename = "ponuda-{$this->quote->id}.pdf";
        $filepath = $directory.'/'.$filename;

        // Spremi PDF u storage
        file_put_contents($filepath, $pdf->output());

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function updateStatus($status)
    {
        if ($this->quote->isConverted()) {
            session()->flash('error', 'Ne možete promijeniti status ponude koja je konvertirana u račun.');

            return;
        }

        $this->quote->update(['status' => $status]);
        $this->quote->refresh();

        session()->flash('message', 'Status ponude je promijenjen u: '.strtoupper($status));
    }

    public function sendPdfEmail()
    {
        try {
            $customer = $this->quote->customer;

            if (! $customer->email) {
                session()->flash('error', 'Kupac nema definiran email.');

                return;
            }

            // Generate PDF
            $pdf = Pdf::loadView('pdf.quote', [
                'quote' => $this->quote,
                'business' => $this->business,
            ]);

            \Mail::to($customer->email)->send(new \App\Mail\QuoteMail($this->quote, $pdf->output()));

            session()->flash('message', 'PDF ponude je uspješno poslan na '.$customer->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Greška pri slanju emaila: '.$e->getMessage());
        }
    }

    public function convertToInvoice()
    {
        try {
            if ($this->quote->isConverted()) {
                session()->flash('error', 'Ponuda je već konvertirana u račun.');

                return;
            }

            $invoice = $this->quote->convertToInvoice();

            session()->flash('message', 'Ponuda je uspješno konvertirana u račun!');

            return redirect()->route('invoices.show', $invoice);
        } catch (\Exception $e) {
            session()->flash('error', 'Greška pri konverziji: '.$e->getMessage());
        }
    }
}
