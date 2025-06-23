<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function render()
    {
        $invoicesQuery = Invoice::with('customer')
            ->when($this->search, function ($query) {
                return $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('oib', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status === 'paid', function ($query) {
                return $query->whereRaw('(paid_cash + paid_transfer) >= total_amount');
            })
            ->when($this->status === 'unpaid', function ($query) {
                return $query->whereRaw('(paid_cash + paid_transfer) < total_amount');
            })
            ->when($this->status === 'overdue', function ($query) {
                return $query->whereRaw('(paid_cash + paid_transfer) < total_amount')
                            ->whereDate('due_date', '<', now());
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('issue_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('issue_date', '<=', $this->dateTo);
            });

        $invoices = $invoicesQuery->latest()->paginate(10);

        $totalAmount = $invoicesQuery->sum('total_amount');
        $paidAmount = $invoicesQuery->sum('paid_cash') + $invoicesQuery->sum('paid_transfer');
        $unpaidAmount = $totalAmount - $paidAmount;

        // Statistika računa
        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::whereRaw('(paid_cash + paid_transfer) >= total_amount')->count(),
            'unpaid' => Invoice::whereRaw('(paid_cash + paid_transfer) < total_amount')->count(),
            'overdue' => Invoice::whereRaw('(paid_cash + paid_transfer) < total_amount')
                        ->whereDate('due_date', '<', now())->count(),
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
        ];

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ])->layout('components.layouts.app', ['title' => 'Računi']);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo']);
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
