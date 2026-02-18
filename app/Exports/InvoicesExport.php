<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $search = null,
        protected $status = null,
        protected $paymentMethod = null,
        protected $dateFrom = null,
        protected $dateTo = null,
        protected $year = null,
        protected $month = null,
        protected $customerId = null
    ) {}

    public function query()
    {
        Log::info('Exporting invoices', [
            'search' => $this->search,
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'year' => $this->year,
            'month' => $this->month,
            'customer_id' => $this->customerId,
        ]);

        return Invoice::query()
            ->with('customer')
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
            ->when($this->customerId, function ($query) {
                return $query->where('customer_id', $this->customerId);
            })
            ->orderByDesc('issue_date');
    }

    public function headings(): array
    {
        return [
            'Broj Računa',
            'Kupac',
            'OIB Kupca',
            'Datum Izdavanja',
            'Datum Dospijeća',
            'Ukupno (€)',
            'Status',
            'Način Plaćanja',
            'Datum Plaćanja',
        ];
    }

    public function map($invoice): array
    {
        $statusLabels = [
            'paid' => 'Plaćen',
            'unpaid' => 'Neplaćen',
            'partial' => 'Djelomično plaćen',
        ];

        $paymentMethodLabels = [
            'cash' => 'Gotovina',
            'transfer' => 'Virman',
            'card' => 'Kartica',
        ];

        return [
            $invoice->invoice_number.'/'.$invoice->invoice_year,
            $invoice->customer->name ?? '-',
            $invoice->customer->oib ?? '-',
            $invoice->issue_date?->format('d.m.Y') ?? '-',
            $invoice->due_date?->format('d.m.Y') ?? '-',
            number_format((float) $invoice->total_amount, 2, ',', '.'),
            $statusLabels[$invoice->status] ?? $invoice->status,
            $paymentMethodLabels[$invoice->payment_method] ?? $invoice->payment_method,
            $invoice->payment_date?->format('d.m.Y') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
