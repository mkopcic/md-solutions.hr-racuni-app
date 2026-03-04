<?php

namespace App\Livewire\Quotes;

use App\Exports\QuotesExport;
use App\Models\Customer;
use App\Models\Quote;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app', ['title' => 'Ponude'])]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $year = '';

    public $month = '';

    public $customer_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'year' => ['except' => ''],
        'month' => ['except' => ''],
        'customer_id' => ['except' => ''],
    ];

    public function render()
    {
        $quotesQuery = Quote::with('customer')
            ->when($this->search, function ($query) {
                return $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('oib', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('quote_number', 'like', '%'.$this->search.'%');
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('issue_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('issue_date', '<=', $this->dateTo);
            })
            ->when($this->year, function ($query) {
                return $query->whereYear('issue_date', $this->year);
            })
            ->when($this->month, function ($query) {
                return $query->whereMonth('issue_date', $this->month);
            })
            ->when($this->customer_id, function ($query) {
                return $query->where('customer_id', $this->customer_id);
            });

        $quotes = $quotesQuery->orderBy('id', 'desc')->paginate(10);

        $totalAmount = $quotesQuery->sum('total_amount');

        // Statistika ponuda
        $stats = [
            'total' => Quote::count(),
            'draft' => Quote::where('status', 'draft')->count(),
            'sent' => Quote::where('status', 'sent')->count(),
            'accepted' => Quote::where('status', 'accepted')->count(),
            'rejected' => Quote::where('status', 'rejected')->count(),
            'expired' => Quote::where('status', 'expired')->count(),
            'converted' => Quote::whereNotNull('converted_to_invoice_id')->count(),
            'totalAmount' => $totalAmount,
        ];

        // Dohvati sve kupce za dropdown
        $customers = Customer::orderBy('name')->get();

        // Dohvati sve godine iz ponuda (SQLite compatible)
        $driver = \DB::getDriverName();
        if ($driver === 'sqlite') {
            $years = Quote::selectRaw("strftime('%Y', issue_date) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
        } else {
            $years = Quote::selectRaw('YEAR(issue_date) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
        }

        return view('livewire.quotes.index', [
            'quotes' => $quotes,
            'stats' => $stats,
            'customers' => $customers,
            'years' => $years,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo', 'year', 'month', 'customer_id']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $quote = Quote::find($id);

        if ($quote->isConverted()) {
            session()->flash('error', 'Ne možete obrisati ponudu koja je konvertirana u račun.');

            return;
        }

        // Obriši sve stavke ponude
        $quote->items()->delete();

        // Obriši ponudu
        $quote->delete();

        session()->flash('message', 'Ponuda je uspješno obrisana.');
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new QuotesExport(
                $this->search,
                $this->status,
                $this->dateFrom,
                $this->dateTo,
                $this->year,
                $this->month,
                $this->customer_id
            ),
            'ponude_'.now()->format('Y-m-d_His').'.xlsx'
        );
    }

    public function exportCsv(): BinaryFileResponse
    {
        return Excel::download(
            new QuotesExport(
                $this->search,
                $this->status,
                $this->dateFrom,
                $this->dateTo,
                $this->year,
                $this->month,
                $this->customer_id
            ),
            'ponude_'.now()->format('Y-m-d_His').'.csv'
        );
    }

    public function updateStatus($id, $status)
    {
        $quote = Quote::find($id);

        if (! $quote) {
            session()->flash('error', 'Ponuda nije pronađena.');

            return;
        }

        if ($quote->isConverted()) {
            session()->flash('error', 'Ne možete promijeniti status ponude koja je konvertirana u račun.');

            return;
        }

        $quote->update(['status' => $status]);

        session()->flash('message', 'Status ponude je uspješno promijenjen.');
    }

    public function sendPdfEmail($quoteId)
    {
        try {
            $user = auth()->user();

            if (! $user || ! $user->email) {
                session()->flash('error', 'Korisnik nije prijavljen ili nema email adresu.');

                return;
            }

            $quote = Quote::with(['customer', 'items'])->find($quoteId);

            if (! $quote) {
                session()->flash('error', 'Ponuda nije pronađena.');

                return;
            }

            $business = \App\Models\Business::first();

            if (! $business) {
                session()->flash('error', 'Podaci o poslovanju nisu konfigurirani.');

                return;
            }

            \Mail::to($user->email)->send(new \App\Mail\QuotePdfMail($quote, $business));

            // Laravel Log
            \Log::info('PDF ponuda je poslana na email iz tablice', [
                'quote_id' => $quote->id,
                'quote_number' => $quote->full_quote_number,
                'customer_name' => $quote->customer->name,
                'email_to' => $user->email,
                'total_amount' => $quote->total_amount,
                'user_id' => $user->id,
            ]);

            // Spatie Activity Log
            activity('quote_email')
                ->causedBy($user)
                ->performedOn($quote)
                ->withProperties([
                    'quote_id' => $quote->id,
                    'quote_number' => $quote->full_quote_number,
                    'customer_name' => $quote->customer->name,
                    'email_to' => $user->email,
                    'total_amount' => $quote->total_amount,
                ])
                ->log('PDF ponuda poslana na email');

            session()->flash('message', 'PDF ponuda uspješno poslana na email: '.$user->email);
        } catch (\Exception $e) {
            \Log::error('Greška pri slanju email-a za ponudu: '.$e->getMessage(), [
                'quote_id' => $quoteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Greška pri slanju email-a: '.$e->getMessage());
        }
    }
}
