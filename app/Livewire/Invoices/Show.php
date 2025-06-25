<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Business;
use App\Models\KprEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Illuminate\Support\Carbon;

class Show extends Component
{
    public Invoice $invoice;
    public $business;

    public function mount(Invoice $invoice)
    {
        \Log::info('Show component mount method called', [
            'invoice_id' => $invoice->id ?? 'not set',
        ]);

        $this->invoice = $invoice->load(['customer', 'items']);
        $this->business = Business::first();

        // Proveriti da li je invoice uspešno učitan
        if (!$this->invoice->exists) {
            \Log::error('Invoice not found in mount method');
            abort(404, 'Račun nije pronađen');
        }

        \Log::info('Show component mount completed successfully', [
            'invoice_id' => $this->invoice->id,
        ]);
    }

    public function render()
    {
        // Debug logging
        \Log::info('Show component render method called', [
            'invoice_exists' => isset($this->invoice),
            'invoice_id' => $this->invoice->id ?? 'not set',
        ]);

        // Proveriti da li je invoice property ispravno setovan
        if (!isset($this->invoice) || !$this->invoice->exists) {
            \Log::error('Invoice not found in Show component');
            abort(404, 'Račun nije pronađen');
        }

        return view('livewire.invoices.show')
            ->layout('components.layouts.app', ['title' => 'Racun #' . $this->invoice->id]);
    }

    public function generatePdf()
    {
        // Kreiraj direktorij ako ne postoji
        $directory = storage_path('app/public/invoices');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'business' => $this->business
        ]);

        $filename = "racun-{$this->invoice->id}.pdf";
        $filepath = $directory . '/' . $filename;

        // Spremi PDF u storage
        file_put_contents($filepath, $pdf->output());

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function createKprEntry()
    {
        // Check if KPR entry already exists
        if ($this->invoice->kprEntry) {
            session()->flash('error', 'KPR zapis već postoji za ovaj račun.');
            return;
        }

        // Get the month from issue_date - handle different date formats
        $month = Carbon::now()->month; // fallback to current month
        if ($this->invoice->issue_date) {
            // Try to get original value before casting
            $issueDate = $this->invoice->getRawOriginal('issue_date') ?? $this->invoice->issue_date;
            if (is_string($issueDate)) {
                $month = (int) date('m', strtotime($issueDate));
            }
        }

        // Create KPR entry
        $kprEntry = KprEntry::create([
            'invoice_id' => $this->invoice->id,
            'month' => $month,
            'amount' => $this->invoice->total_amount
        ]);

        if ($kprEntry) {
            session()->flash('message', 'KPR zapis je uspješno kreiran.');
        } else {
            session()->flash('error', 'Došlo je do greške pri kreiranju KPR zapisa.');
        }
    }

    public function markAsPaid($type, $amount)
    {
        $validTypes = ['cash', 'transfer'];
        if (!in_array($type, $validTypes) || !is_numeric($amount) || $amount < 0) {
            session()->flash('error', 'Neispravan iznos ili način plaćanja.');
            return;
        }

        $updateFields = [];
        if ($type === 'cash') {
            $updateFields['paid_cash'] = $this->invoice->paid_cash + $amount;
        } else {
            $updateFields['paid_transfer'] = $this->invoice->paid_transfer + $amount;
        }

        // Ako je ukupno plaćanje jednako ili veće od ukupnog iznosa, označi kao plaćeno
        $totalPaid = $this->invoice->paid_cash + $this->invoice->paid_transfer + $amount;
        if ($totalPaid >= $this->invoice->total_amount) {
            $updateFields['status'] = 'paid';
            $updateFields['paid_at'] = Carbon::now();
        }

        $this->invoice->update($updateFields);
        $this->invoice->refresh();

        session()->flash('message', 'Uspješno ste evidentirali plaćanje.');
    }
}
