<?php

namespace App\Livewire\Eracun\Logs;

use App\Enums\EracunStatus;
use App\Enums\FinaStatus;
use App\Models\EracunLog;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'e-Račun Logovi'])]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $direction = '';

    public $status = '';

    public $finaStatus = '';

    public $dateFrom = '';

    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'direction' => ['except' => ''],
        'status' => ['except' => ''],
        'finaStatus' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function render()
    {
        $logsQuery = EracunLog::with(['invoice.customer', 'incomingInvoice'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('message_id', 'like', '%'.$this->search.'%')
                        ->orWhere('fina_invoice_id', 'like', '%'.$this->search.'%')
                        ->orWhereHas('invoice.customer', function ($customer) {
                            $customer->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('incomingInvoice', function ($incoming) {
                            $incoming->where('supplier_name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->direction, function ($query) {
                return $query->where('direction', $this->direction);
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

        $logs = $logsQuery->orderBy('id', 'desc')->paginate(20);

        // Statistika
        $stats = [
            'total' => EracunLog::count(),
            'outgoing' => EracunLog::outgoing()->count(),
            'incoming' => EracunLog::incoming()->count(),
            'failed' => EracunLog::failed()->count(),
            'pending_retry' => EracunLog::pendingRetry()->count(),
        ];

        return view('livewire.eracun.logs.index', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'direction', 'status', 'finaStatus', 'dateFrom', 'dateTo']);
        $this->resetPage();
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

        $this->dispatch('show-xml-modal', xml: $xml, title: strtoupper($type).' XML - Log #'.$logId);
    }

    public function deleteLog($logId)
    {
        try {
            $log = EracunLog::findOrFail($logId);
            $log->delete();

            session()->flash('message', 'Log je uspješno obrisan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }
}
