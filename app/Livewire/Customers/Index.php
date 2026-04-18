<?php

namespace App\Livewire\Customers;

use App\Exports\CustomersExport;
use App\Exports\CustomersReportExport;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app', ['title' => 'Kupci'])]
class Index extends Component
{
    use WithPagination;

    public $name;

    public $address;

    public $city;

    public $oib;

    public $editingCustomerId;

    public $viewingCustomerId;

    public $search = '';

    // Tab navigacija
    public $activeTab = 'list';

    // Filteri za izvještaj
    public $reportYear = null;

    public $reportMonth = null;

    public $reportSearch = '';

    public function mount()
    {
        // Postavi trenutnu godinu kao default za izvještaj
        $this->reportYear = 'all';
        $this->reportMonth = null;

        // Ako je create=1 u URL-u, otvori dialog odmah
        if (request()->get('create') == '1') {
            $this->create();
        }
    }

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'address' => 'required|max:255',
        'city' => 'required|max:100',
        'oib' => 'required|size:11',
    ];

    protected $messages = [
        'name.required' => 'Naziv je obavezan',
        'name.min' => 'Naziv mora imati barem 2 znaka',
        'address.required' => 'Adresa je obavezna',
        'city.required' => 'Grad je obavezan',
        'oib.required' => 'OIB je obavezan',
        'oib.size' => 'OIB mora imati 11 znamenki',
    ];

    public function render()
    {
        if ($this->activeTab === 'report') {
            return $this->renderReport();
        }

        $customers = Customer::query()
            ->withCount('invoices')
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('oib', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20);

        $viewingCustomer = $this->viewingCustomerId
            ? Customer::withCount('invoices')->find($this->viewingCustomerId)
            : null;

        $stats = $this->getStats();

        return view('livewire.customers.index', [
            'customers' => $customers,
            'viewingCustomer' => $viewingCustomer,
            'stats' => $stats,
        ]);
    }

    private function getStats(): array
    {
        $total = Customer::count();
        $withInvoices = Customer::whereHas('invoices')->count();

        return [
            'total' => $total,
            'with_invoices' => $withInvoices,
            'without_invoices' => $total - $withInvoices,
            'cities' => Customer::whereNotNull('city')->where('city', '!=', '')->distinct('city')->count('city'),
        ];
    }

    public function renderReport()
    {
        $months = [
            1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
            5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
            9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac',
        ];

        // Dohvati sve godine iz računa
        $years = Invoice::selectRaw('YEAR(issue_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [Carbon::now()->year];
        }

        // Dinamički build SQL uvjeta za conditional aggregation
        $yearCondition = '';
        $monthCondition = '';

        if ($this->reportYear && $this->reportYear !== 'all') {
            $yearCondition = ' AND YEAR(invoices.issue_date) = '.(int) $this->reportYear;
        }

        if ($this->reportMonth) {
            $monthCondition = ' AND MONTH(invoices.issue_date) = '.(int) $this->reportMonth;
        }

        // Pripremi query za kupce - svi kupci s agregiranim podacima
        $customersQuery = Customer::query()
            ->select('customers.*')
            ->selectRaw("COUNT(CASE WHEN invoices.id IS NOT NULL{$yearCondition}{$monthCondition} THEN 1 END) as invoices_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN invoices.id IS NOT NULL{$yearCondition}{$monthCondition} THEN invoices.total_amount ELSE 0 END), 0) as total_revenue")
            ->leftJoin('invoices', 'customers.id', '=', 'invoices.customer_id')
            ->when($this->reportSearch, function ($query) {
                return $query->where(function ($query) {
                    $query->where('customers.name', 'like', '%'.$this->reportSearch.'%')
                        ->orWhere('customers.oib', 'like', '%'.$this->reportSearch.'%')
                        ->orWhere('customers.city', 'like', '%'.$this->reportSearch.'%');
                });
            })
            ->groupBy('customers.id', 'customers.name', 'customers.address', 'customers.city', 'customers.oib', 'customers.created_at', 'customers.updated_at')
            ->orderByRaw('total_revenue DESC, customers.name ASC');

        $customersReport = $customersQuery->paginate(10);

        // Ukupni prihod i statistika
        $totalRevenueQuery = Invoice::query();

        if ($this->reportYear && $this->reportYear !== 'all') {
            $totalRevenueQuery->whereYear('issue_date', $this->reportYear);
        }

        if ($this->reportMonth) {
            $totalRevenueQuery->whereMonth('issue_date', $this->reportMonth);
        }

        $totalRevenue = $totalRevenueQuery->sum('total_amount');

        // Broj aktivnih kupaca (onih s računima u periodu)
        $activeCustomersQuery = Customer::query()
            ->whereHas('invoices', function ($query) {
                if ($this->reportYear && $this->reportYear !== 'all') {
                    $query->whereYear('issue_date', $this->reportYear);
                }

                if ($this->reportMonth) {
                    $query->whereMonth('issue_date', $this->reportMonth);
                }
            });

        $activeCustomersCount = $activeCustomersQuery->count();

        $viewingCustomer = $this->viewingCustomerId
            ? Customer::withCount('invoices')->find($this->viewingCustomerId)
            : null;

        return view('livewire.customers.index', [
            'customers' => $customersReport,
            'months' => $months,
            'years' => $years,
            'totalRevenue' => $totalRevenue,
            'activeCustomersCount' => $activeCustomersCount,
            'viewingCustomer' => $viewingCustomer,
            'stats' => $this->getStats(),
        ]);
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedReportYear()
    {
        $this->resetPage();
    }

    public function updatedReportMonth()
    {
        $this->resetPage();
    }

    public function updatedReportSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->editingCustomerId = null;
        $this->dispatch('open-customer-dialog');
    }

    public function view($id)
    {
        $customer = Customer::findOrFail($id);
        $this->viewingCustomerId = $id;
        $this->name = $customer->name;
        $this->address = $customer->address;
        $this->city = $customer->city;
        $this->oib = $customer->oib;

        $this->dispatch('open-customer-view-dialog');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $this->editingCustomerId = $id;
        $this->name = $customer->name;
        $this->address = $customer->address;
        $this->city = $customer->city;
        $this->oib = $customer->oib;

        $this->dispatch('open-customer-dialog');
    }

    public function save()
    {
        $this->validate();

        Customer::updateOrCreate(['id' => $this->editingCustomerId], [
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'oib' => $this->oib,
        ]);

        session()->flash('message', $this->editingCustomerId ? 'Kupac uspješno ažuriran.' : 'Kupac uspješno dodan.');

        $this->reset(['editingCustomerId', 'name', 'address', 'city', 'oib']);
        $this->dispatch('close-customer-dialog');
    }

    public function delete($id)
    {
        $customer = Customer::find($id);

        // Provjeri postoje li računi za ovog kupca
        if ($customer->invoices()->count() > 0) {
            session()->flash('error', 'Nije moguće obrisati kupca jer ima povezane račune.');

            return;
        }

        $customer->delete();
        session()->flash('message', 'Kupac uspješno obrisan.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->address = '';
        $this->city = '';
        $this->oib = '';
        $this->editingCustomerId = null;
        $this->viewingCustomerId = null;
    }

    public function closeDialog()
    {
        $this->resetInputFields();
        $this->dispatch('close-customer-dialog');
    }

    public function closeViewDialog()
    {
        $this->resetInputFields();
        $this->dispatch('close-customer-view-dialog');
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new CustomersExport($this->search),
            'kupci_'.now()->format('Y-m-d_His').'.xlsx'
        );
    }

    public function exportCsv(): BinaryFileResponse
    {
        return Excel::download(
            new CustomersExport($this->search),
            'kupci_'.now()->format('Y-m-d_His').'.csv'
        );
    }

    public function exportReportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new CustomersReportExport($this->reportYear, $this->reportMonth, $this->reportSearch),
            'izvjestaj_po_kupcima_'.now()->format('Y-m-d_His').'.xlsx'
        );
    }

    public function exportReportCsv(): BinaryFileResponse
    {
        return Excel::download(
            new CustomersReportExport($this->reportYear, $this->reportMonth, $this->reportSearch),
            'izvjestaj_po_kupcima_'.now()->format('Y-m-d_His').'.csv'
        );
    }
}
