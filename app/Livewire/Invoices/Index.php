<?php

namespace App\Livewire\Invoices;

use App\Exports\InvoicesExport;
use App\Mail\InvoicePdfMail;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app', ['title' => 'Računi'])]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $paymentMethod = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $year = '';

    public $month = '';

    public $customer_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'year' => ['except' => ''],
        'month' => ['except' => ''],
        'customer_id' => ['except' => ''],
    ];

    public function render()
    {
        $invoicesQuery = Invoice::with('customer')
            ->when($this->search, function ($query) {
                return $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('oib', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status === 'paid', function ($query) {
                return $query->where('status', 'paid');
            })
            ->when($this->status === 'unpaid', function ($query) {
                return $query->whereIn('status', ['unpaid', 'partial'])
                    ->where(function ($q) {
                        $q->whereDate('due_date', '>=', now())
                            ->orWhereNull('due_date');
                    });
            })
            ->when($this->status === 'overdue', function ($query) {
                return $query->whereIn('status', ['unpaid', 'partial'])
                    ->whereDate('due_date', '<', now());
            })
            ->when($this->paymentMethod, function ($query) {
                return $query->where('payment_method', $this->paymentMethod);
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

        $invoices = $invoicesQuery->orderBy('id', 'desc')->paginate(10);

        $totalAmount = $invoicesQuery->sum('total_amount');
        $paidAmount = $invoicesQuery->sum('paid_cash') + $invoicesQuery->sum('paid_transfer');
        $unpaidAmount = $totalAmount - $paidAmount;

        // Statistika računa
        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'unpaid' => Invoice::whereIn('status', ['unpaid', 'partial'])->count(),
            'overdue' => Invoice::whereIn('status', ['unpaid', 'partial'])
                ->whereDate('due_date', '<', now())->count(),
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
        ];

        // Dohvati sve kupce za dropdown
        $customers = Customer::orderBy('name')->get();

        // Dohvati sve godine iz računa (SQLite compatible)
        $driver = \DB::getDriverName();
        if ($driver === 'sqlite') {
            $years = Invoice::selectRaw("strftime('%Y', issue_date) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
        } else {
            $years = Invoice::selectRaw('YEAR(issue_date) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
        }

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'customers' => $customers,
            'years' => $years,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'paymentMethod', 'dateFrom', 'dateTo', 'year', 'month', 'customer_id']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $invoice = Invoice::find($id);

        // Provjeri jel se može obrisati (npr. ima li povezanih KPR zapisa koje treba ažurirati)
        if ($invoice->kprEntry) {
            $invoice->kprEntry->delete();
        }

        // Obriši sve stavke računa
        $invoice->items()->delete();

        // Obriši račun
        $invoice->delete();

        session()->flash('message', 'Račun je uspješno obrisan.');
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new InvoicesExport(
                $this->search,
                $this->status,
                $this->paymentMethod,
                $this->dateFrom,
                $this->dateTo,
                $this->year,
                $this->month,
                $this->customer_id
            ),
            'racuni_'.now()->format('Y-m-d_His').'.xlsx'
        );
    }

    public function exportCsv(): BinaryFileResponse
    {
        return Excel::download(
            new InvoicesExport(
                $this->search,
                $this->status,
                $this->paymentMethod,
                $this->dateFrom,
                $this->dateTo,
                $this->year,
                $this->month,
                $this->customer_id
            ),
            'racuni_'.now()->format('Y-m-d_His').'.csv'
        );
    }

    public function sendPdfEmail($invoiceId)
    {
        try {
            $user = auth()->user();

            if (! $user || ! $user->email) {
                session()->flash('error', 'Korisnik nije prijavljen ili nema email adresu.');

                return;
            }

            $invoice = Invoice::with(['customer', 'items'])->find($invoiceId);

            if (! $invoice) {
                session()->flash('error', 'Račun nije pronađen.');

                return;
            }

            $business = Business::first();

            if (! $business) {
                session()->flash('error', 'Podaci o poslovanju nisu konfigurirani.');

                return;
            }

            Mail::to($user->email)->send(new InvoicePdfMail($invoice, $business));

            // Laravel Log
            \Log::info('PDF račun je poslan na email iz tablice', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_invoice_number,
                'customer_name' => $invoice->customer->name,
                'email_to' => $user->email,
                'total_amount' => $invoice->total_amount,
                'user_id' => $user->id,
            ]);

            // Spatie Activity Log
            activity('invoice_email')
                ->causedBy($user)
                ->performedOn($invoice)
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->full_invoice_number,
                    'customer_name' => $invoice->customer->name,
                    'email_to' => $user->email,
                    'total_amount' => $invoice->total_amount,
                    'source' => 'invoice_table',
                ])
                ->log("PDF račun #{$invoice->full_invoice_number} poslan na email {$user->email} iz tablice računa");

            session()->flash('message', "PDF račun #{$invoice->full_invoice_number} je uspješno poslan na email: {$user->email}");
        } catch (\Exception $e) {
            \Log::error('Greška pri slanju PDF računa na email iz tablice', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Spatie Activity Log za grešku
            if (auth()->check()) {
                $invoice = Invoice::find($invoiceId);
                activity('invoice_email')
                    ->causedBy(auth()->user())
                    ->performedOn($invoice)
                    ->withProperties([
                        'invoice_id' => $invoiceId,
                        'error' => $e->getMessage(),
                        'source' => 'invoice_table',
                    ])
                    ->log("Greška pri slanju PDF računa (ID: {$invoiceId}) na email iz tablice");
            }

            session()->flash('error', 'Greška pri slanju emaila: '.$e->getMessage());
        }
    }
}
