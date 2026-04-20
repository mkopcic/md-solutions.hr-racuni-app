<?php

namespace App\Livewire\Invoices;

use App\Enums\FinaStatus;
use App\Mail\InvoicePdfMail;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\KprEntry;
use App\Services\EracunFina\EracunService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public $business;

    public function mount(Invoice $invoice)
    {
        \Log::info('Show component mount method called', [
            'invoice_id' => $invoice->id ?? 'not set',
        ]);

        $this->invoice = $invoice->load(['customer', 'items', 'latestEracunLog']);
        $this->business = Business::first();

        // Proveriti da li je invoice uspešno učitan
        if (! $this->invoice->exists) {
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
        if (! isset($this->invoice) || ! $this->invoice->exists) {
            \Log::error('Invoice not found in Show component');
            abort(404, 'Račun nije pronađen');
        }

        return view('livewire.invoices.show')
            ->layout('components.layouts.app', ['title' => 'Racun #'.$this->invoice->id]);
    }

    public function generatePdf()
    {
        // Kreiraj direktorij ako ne postoji
        $directory = storage_path('app/public/invoices');
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'business' => $this->business,
        ]);

        $filename = "racun-{$this->invoice->id}.pdf";
        $filepath = $directory.'/'.$filename;

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
            'amount' => $this->invoice->total_amount,
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
        if (! in_array($type, $validTypes) || ! is_numeric($amount) || $amount < 0) {
            session()->flash('error', 'Neispravan iznos ili način plaćanja.');

            return;
        }

        // Ažuriraj plaćeni iznos
        if ($type === 'cash') {
            $this->invoice->paid_cash += $amount;
        } else {
            $this->invoice->paid_transfer += $amount;
        }

        $this->invoice->save();

        // Automatski ažuriraj status na temelju matematike
        $this->invoice->updateStatus();
        $this->invoice->refresh();

        session()->flash('message', 'Uspješno ste evidentirali plaćanje.');
    }

    public function syncStatus()
    {
        $this->invoice->updateStatus();
        $this->invoice->refresh();

        session()->flash('message', 'Status računa je ažuriran.');
    }

    public function sendToEracun()
    {
        try {
            $invoice = $this->invoice->load(['items', 'customer']);
            $service = app(EracunService::class);

            $result = $service->sendInvoice($invoice);

            if ($result['success']) {
                session()->flash('message', 'Račun je uspješno poslan na FINA e-Račun sustav!');
                $this->invoice->refresh();
            } else {
                $errorMessage = $result['error'] ?? $result['response']['error'] ?? 'Nepoznata greška';
                session()->flash('error', 'Greška pri slanju: '.$errorMessage);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function checkEracunStatus()
    {
        try {
            $log = $this->invoice->latestEracunLog;

            if (! $log || ! $log->fina_invoice_id) {
                session()->flash('error', 'Račun još nije poslan ili nema FINA ID.');

                return;
            }

            $service = app(EracunService::class);

            $result = $service->getInvoiceStatus(
                $log->fina_invoice_id,
                $this->invoice->issue_date->year
            );

            if ($result['success']) {
                // Ažuriraj status u logu
                $log->update([
                    'fina_status' => FinaStatus::from($result['response']['status']),
                    'status_checked_at' => now(),
                ]);

                $this->invoice->refresh();
                session()->flash('message', 'Status ažuriran: '.$result['response']['status']);
            } else {
                $errorMessage = $result['error'] ?? $result['response']['error'] ?? 'Nepoznata greška';
                session()->flash('error', 'Greška pri provjeri statusa: '.$errorMessage);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function viewEracunXml($type)
    {
        $log = $this->invoice->latestEracunLog;

        if (! $log) {
            session()->flash('error', 'Nema e-Račun loga za ovaj račun.');

            return;
        }

        $xml = match ($type) {
            'ubl' => $log->ubl_xml,
            'request' => $log->request_xml,
            'response' => $log->response_xml,
            default => null,
        };

        $this->dispatch('show-xml-modal', xml: $xml, title: strtoupper($type).' XML');
    }

    public function sendPdfEmail()
    {
        try {
            $user = auth()->user();

            if (! $user || ! $user->email) {
                session()->flash('error', 'Korisnik nije prijavljen ili nema email adresu.');

                return;
            }

            Mail::to($user->email)->send(new InvoicePdfMail($this->invoice, $this->business));

            // Laravel Log
            \Log::info('PDF račun je poslan na email', [
                'invoice_id' => $this->invoice->id,
                'invoice_number' => $this->invoice->full_invoice_number,
                'customer_name' => $this->invoice->customer->name,
                'email_to' => $user->email,
                'total_amount' => $this->invoice->total_amount,
                'user_id' => $user->id,
            ]);

            // Spatie Activity Log
            activity('invoice_email')
                ->causedBy($user)
                ->performedOn($this->invoice)
                ->withProperties([
                    'invoice_id' => $this->invoice->id,
                    'invoice_number' => $this->invoice->full_invoice_number,
                    'customer_name' => $this->invoice->customer->name,
                    'email_to' => $user->email,
                    'total_amount' => $this->invoice->total_amount,
                ])
                ->log("PDF račun #{$this->invoice->full_invoice_number} poslan na email {$user->email}");

            session()->flash('message', "PDF račun je uspješno poslan na email: {$user->email}");
        } catch (\Exception $e) {
            \Log::error('Greška pri slanju PDF računa na email', [
                'invoice_id' => $this->invoice->id,
                'invoice_number' => $this->invoice->full_invoice_number ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Spatie Activity Log za grešku
            if (auth()->check()) {
                activity('invoice_email')
                    ->causedBy(auth()->user())
                    ->performedOn($this->invoice)
                    ->withProperties([
                        'invoice_id' => $this->invoice->id,
                        'error' => $e->getMessage(),
                    ])
                    ->log("Greška pri slanju PDF računa #{$this->invoice->full_invoice_number} na email");
            }

            session()->flash('error', 'Greška pri slanju emaila: '.$e->getMessage());
        }
    }
}
