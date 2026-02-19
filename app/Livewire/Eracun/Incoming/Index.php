<?php

namespace App\Livewire\Eracun\Incoming;

use App\Enums\IncomingInvoiceStatus;
use App\Models\IncomingInvoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Ulazni e-Računi'])]
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
        $invoices = IncomingInvoice::query()
            ->with(['reviewedBy', 'approvedBy', 'rejectedBy', 'items'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('supplier_name', 'like', '%'.$this->search.'%')
                        ->orWhere('supplier_oib', 'like', '%'.$this->search.'%')
                        ->orWhere('invoice_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, function ($query) {
                return $query->where('status', IncomingInvoiceStatus::from($this->status));
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('issue_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('issue_date', '<=', $this->dateTo);
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Statistika
        $stats = [
            'total' => IncomingInvoice::count(),
            'received' => IncomingInvoice::where('status', IncomingInvoiceStatus::RECEIVED)->count(),
            'pending_review' => IncomingInvoice::where('status', IncomingInvoiceStatus::PENDING_REVIEW)->count(),
            'approved' => IncomingInvoice::where('status', IncomingInvoiceStatus::APPROVED)->count(),
            'rejected' => IncomingInvoice::where('status', IncomingInvoiceStatus::REJECTED)->count(),
            'paid' => IncomingInvoice::where('status', IncomingInvoiceStatus::PAID)->count(),
            'totalAmount' => IncomingInvoice::whereIn('status', [
                IncomingInvoiceStatus::APPROVED,
                IncomingInvoiceStatus::PAID,
            ])->sum('total_amount'),
            'unpaidAmount' => IncomingInvoice::where('status', IncomingInvoiceStatus::APPROVED)->sum('total_amount'),
        ];

        return view('livewire.eracun.incoming.index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function quickApprove($invoiceId)
    {
        try {
            $invoice = IncomingInvoice::findOrFail($invoiceId);

            if ($invoice->approve(auth()->id())) {
                session()->flash('message', 'Račun je uspješno odobren.');
            } else {
                session()->flash('error', 'Nije moguće odobriti račun u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function quickReject($invoiceId)
    {
        try {
            $invoice = IncomingInvoice::findOrFail($invoiceId);

            if ($invoice->reject(auth()->id(), 'Brzo odbijanje')) {
                session()->flash('message', 'Račun je odbijen.');
            } else {
                session()->flash('error', 'Nije moguće odbiti račun u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function markAsPaid($invoiceId)
    {
        try {
            $invoice = IncomingInvoice::findOrFail($invoiceId);

            if ($invoice->markAsPaid()) {
                session()->flash('message', 'Račun je označen kao plaćen.');
            } else {
                session()->flash('error', 'Nije moguće označiti račun kao plaćen u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function viewXml($invoiceId)
    {
        $invoice = IncomingInvoice::findOrFail($invoiceId);
        $this->dispatch('show-xml-modal', xml: $invoice->ubl_xml, title: 'UBL XML - '.$invoice->invoice_number);
    }
}
