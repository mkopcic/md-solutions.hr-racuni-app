<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $search = null
    ) {}

    public function query()
    {
        Log::info('Exporting customers', [
            'search' => $this->search,
        ]);

        return Customer::query()
            ->withCount('invoices')
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('oib', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Naziv',
            'OIB',
            'Adresa',
            'Grad',
            'Broj Računa',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->oib,
            $customer->address,
            $customer->city,
            $customer->invoices_count,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
