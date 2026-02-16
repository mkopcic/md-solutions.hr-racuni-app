<?php

namespace App\Livewire\KPR;

use App\Models\Invoice;
use App\Models\KprEntry;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $year;

    public $month;

    public function mount()
    {
        // Postavi trenutnu godinu i mjesec
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->month;
    }

    public function render()
    {
        $months = [
            1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
            5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
            9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac',
        ];

        // Dohvati sve KPR unose za odabrani mjesec i godinu
        $entries = KprEntry::with('invoice.customer')
            ->where('month', $this->month)
            ->whereHas('invoice', function ($query) {
                $query->whereYear('issue_date', $this->year);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Ukupna zarada u mjesecu
        $totalMonthlyAmount = KprEntry::where('month', $this->month)
            ->whereHas('invoice', function ($query) {
                $query->whereYear('issue_date', $this->year);
            })
            ->sum('amount');

        // Ukupna zarada u godini
        $totalYearlyAmount = KprEntry::whereHas('invoice', function ($query) {
            $query->whereYear('issue_date', $this->year);
        })
            ->sum('amount');

        // Dohvati sve godine za koje imamo KPR unose
        $years = Invoice::selectRaw('YEAR(issue_date) as year')
            ->whereHas('kprEntry')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Ako nema godina u bazi, dodaj trenutnu
        if (empty($years)) {
            $years = [Carbon::now()->year];
        }

        return view('livewire.k-p-r.index', [
            'entries' => $entries,
            'months' => $months,
            'years' => $years,
            'totalMonthlyAmount' => $totalMonthlyAmount,
            'totalYearlyAmount' => $totalYearlyAmount,
        ])->layout('components.layouts.app', ['title' => 'Knjiga prometa']);
    }

    public function updateFilters()
    {
        $this->resetPage();
    }

    public function updatedMonth()
    {
        $this->resetPage();
    }

    public function updatedYear()
    {
        $this->resetPage();
    }

    // Metoda za automatsko generiranje KPR unosa iz računa
    public function generateKprEntries()
    {
        // Dohvati sve račune koji nemaju KPR unose
        $invoices = Invoice::whereDoesntHave('kprEntry')
            ->where('issue_date', '<=', now())
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            // Use the current month as a fallback if we can't extract it
            $month = (int) date('m');

            // Try to get the month from the invoice date if available
            if ($invoice->issue_date) {
                $month = (int) date('m', $invoice->getRawOriginal('issue_date') ?
                    strtotime($invoice->getRawOriginal('issue_date')) : time());
            }

            KprEntry::create([
                'invoice_id' => $invoice->id,
                'month' => $month,
                'amount' => $invoice->total_amount,
            ]);

            $count++;
        }

        session()->flash('message', "Uspješno generirano $count novih KPR unosa.");
    }

    // Metoda za brisanje unosa iz KPR
    public function deleteEntry($id)
    {
        $entry = KprEntry::findOrFail($id);
        $entry->delete();

        session()->flash('message', 'KPR unos uspješno obrisan.');
    }
}
