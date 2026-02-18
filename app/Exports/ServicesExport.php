<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $search = null
    ) {}

    public function query()
    {
        Log::info('Exporting services', [
            'search' => $this->search,
        ]);

        return Service::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Naziv',
            'Opis',
            'Cijena (€)',
            'Jedinica',
            'Status',
        ];
    }

    public function map($service): array
    {
        return [
            $service->name,
            $service->description ?? '-',
            number_format((float) $service->price, 2, ',', '.'),
            $service->unit,
            $service->active ? 'Aktivna' : 'Neaktivna',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
