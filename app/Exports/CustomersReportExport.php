<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $reportYear = null,
        protected $reportMonth = null,
        protected $reportSearch = null
    ) {}

    public function collection()
    {
        // Dinamički build SQL uvjeta za conditional aggregation
        $yearCondition = '';
        $monthCondition = '';

        if ($this->reportYear && $this->reportYear !== 'all') {
            $yearCondition = ' AND YEAR(invoices.issue_date) = '.(int) $this->reportYear;
        }

        if ($this->reportMonth) {
            $monthCondition = ' AND MONTH(invoices.issue_date) = '.(int) $this->reportMonth;
        }

        // Dohvati sve kupce s agregiranim podacima
        $customers = Customer::query()
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
            ->orderByRaw('total_revenue DESC, customers.name ASC')
            ->get();

        return $customers;
    }

    public function headings(): array
    {
        return [
            'Naziv',
            'OIB',
            'Adresa',
            'Grad',
            'Broj Računa',
            'Ukupan Prihod (€)',
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
            number_format((float) $customer->total_revenue, 2, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
