<?php

namespace App\Livewire\Invoices;

use App\Models\Customer;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

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

        // Dohvati sve godine iz računa
        $years = Invoice::selectRaw('YEAR(issue_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

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
}
