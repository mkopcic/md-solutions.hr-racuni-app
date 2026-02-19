<?php

namespace App\Livewire\Eracun\Outgoing;

use App\Enums\EracunStatus;
use App\Enums\FinaStatus;
use App\Models\EracunLog;
use App\Models\Invoice;
use App\Services\EracunFina\EracunService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Izlazni e-Računi'])]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $finaStatus = '';

    public $dateFrom = '';

    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'finaStatus' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function render()
    {
        $logsQuery = EracunLog::with(['invoice.customer'])
            ->outgoing()
            ->when($this->search, function ($query) {
                return $query->whereHas('invoice.customer', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('oib', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, function ($query) {
                return $query->where('status', EracunStatus::from($this->status));
            })
            ->when($this->finaStatus, function ($query) {
                return $query->where('fina_status', FinaStatus::from($this->finaStatus));
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('created_at', '<=', $this->dateTo);
            });

        $logs = $logsQuery->orderBy('id', 'desc')->paginate(15);

        // Statistika
        $stats = [
            'total' => EracunLog::outgoing()->count(),
            'pending' => EracunLog::outgoing()->where('status', EracunStatus::PENDING)->count(),
            'sent' => EracunLog::outgoing()->where('status', EracunStatus::SENT)->count(),
            'accepted' => EracunLog::outgoing()->where('status', EracunStatus::ACCEPTED)->count(),
            'rejected' => EracunLog::outgoing()->where('status', EracunStatus::REJECTED)->count(),
            'failed' => EracunLog::outgoing()->where('status', EracunStatus::FAILED)->count(),
        ];

        return view('livewire.eracun.outgoing.index', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'finaStatus', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function sendInvoice($invoiceId)
    {
        try {
            $invoice = Invoice::with(['items', 'customer', 'business'])->findOrFail($invoiceId);
            $service = app(EracunService::class);

            $result = $service->sendInvoice($invoice);

            if ($result['success']) {
                session()->flash('message', 'Račun je uspješno poslan na FINA e-Račun sustav!');
            } else {
                session()->flash('error', 'Greška pri slanju: '.$result['error']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function retryInvoice($logId)
    {
        try {
            $log = EracunLog::findOrFail($logId);

            if ($log->retry_count >= 3) {
                session()->flash('error', 'Maksimalan broj pokušaja je dostignut (3).');

                return;
            }

            $invoice = $log->invoice;
            $service = app(EracunService::class);

            $result = $service->sendInvoice($invoice);

            if ($result['success']) {
                session()->flash('message', 'Račun je uspješno ponovno poslan!');
            } else {
                session()->flash('error', 'Greška pri ponovnom slanju: '.$result['error']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function checkStatus($logId)
    {
        try {
            $log = EracunLog::findOrFail($logId);

            if (! $log->fina_invoice_id) {
                session()->flash('error', 'Račun još nije poslan ili nema FINA ID.');

                return;
            }

            $service = app(EracunService::class);
            $invoice = $log->invoice;

            $result = $service->getInvoiceStatus(
                $log->fina_invoice_id,
                $invoice->issue_date->year
            );

            if ($result['success']) {
                // Ažuriraj status u logu
                $log->update([
                    'fina_status' => FinaStatus::from($result['response']['status']),
                    'status_checked_at' => now(),
                ]);

                session()->flash('message', 'Status ažuriran: '.$result['response']['status']);
            } else {
                session()->flash('error', 'Greška pri provjeri statusa: '.$result['error']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function viewXml($logId, $type)
    {
        $log = EracunLog::findOrFail($logId);

        $xml = match ($type) {
            'ubl' => $log->ubl_xml,
            'request' => $log->request_xml,
            'response' => $log->response_xml,
            default => null,
        };

        $this->dispatch('show-xml-modal', xml: $xml, title: strtoupper($type).' XML');
    }
}
