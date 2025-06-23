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
        $this->invoice = $invoice->load(['customer', 'items']);
        $this->business = Business::first();
    }

    public function render()
    {
        return view('livewire.invoices.show')
            ->layout('components.layouts.app', ['title' => 'Račun #' . $this->invoice->id]);
    }

    public function generatePdf()
    {
        $pdf = PDF::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'business' => $this->business
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "racun-{$this->invoice->id}.pdf");
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
