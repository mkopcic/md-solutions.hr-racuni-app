<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Quote;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuotesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $search = null,
        protected $status = null,
        protected $dateFrom = null,
        protected $dateTo = null,
        protected $year = null,
        protected $month = null,
        protected $customerId = null
    ) {}

    public function query()
    {
        Log::info('Exporting quotes', [
            'search' => $this->search,
            'status' => $this->status,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'year' => $this->year,
            'month' => $this->month,
            'customer_id' => $this->customerId,
        ]);

        return Quote::query()
            ->with('customer')
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
            ->when($this->customerId, function ($query) {
                return $query->where('customer_id', $this->customerId);
            })
            ->orderBy('issue_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Broj ponude',
            'Kupac',
            'OIB kupca',
            'Datum izdavanja',
            'Datum isporuke',
            'Vrijedi do',
            'Status',
            'Osnovica (€)',
            'PDV (€)',
            'Ukupno (€)',
            'Konvertirano u račun',
            'Napomena',
            'Interne napomene',
        ];
    }

    public function map($quote): array
    {
        return [
            $quote->id,
            $quote->full_quote_number,
            $quote->customer->name ?? '',
            $quote->customer->oib ?? '',
            $quote->issue_date ? $quote->issue_date->format('d.m.Y') : '',
            $quote->delivery_date ? $quote->delivery_date->format('d.m.Y') : '',
            $quote->valid_until ? $quote->valid_until->format('d.m.Y') : '',
            strtoupper($quote->status),
            number_format($quote->subtotal, 2, ',', '.'),
            number_format($quote->tax_total, 2, ',', '.'),
            number_format($quote->total_amount, 2, ',', '.'),
            $quote->isConverted() ? 'DA' : 'NE',
            $quote->note ?? '',
            $quote->internal_notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Stiliziranje zaglavlja
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }
}
