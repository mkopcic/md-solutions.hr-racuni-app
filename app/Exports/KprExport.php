<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\KprEntry;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KprExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $month = null,
        protected $year = null
    ) {}

    public function query()
    {
        return KprEntry::query()
            ->with(['invoice' => function ($query) {
                $query->with('customer');
            }])
            ->when($this->month !== null, function ($query) {
                $query->where('month', $this->month);
            })
            ->whereHas('invoice', function ($query) {
                $query->whereYear('issue_date', $this->year);
            })
            ->leftJoin('invoices', 'kpr_entries.invoice_id', '=', 'invoices.id')
            ->orderBy('invoices.issue_date', 'desc')
            ->select('kpr_entries.*');
    }

    public function headings(): array
    {
        return [
            'Datum',
            'Broj računa',
            'Kupac',
            'OIB',
            'Opis',
            'Iznos (€)',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->invoice ? $entry->invoice->issue_date->format('d.m.Y') : '',
            $entry->invoice ? $entry->invoice->invoice_number : '',
            $entry->invoice && $entry->invoice->customer ? $entry->invoice->customer->name : '',
            $entry->invoice && $entry->invoice->customer ? $entry->invoice->customer->oib : '',
            $entry->description ?? '',
            number_format((float) $entry->amount, 2, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
