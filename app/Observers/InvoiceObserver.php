<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\KprEntry;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Automatski kreiraj KPR zapis kada se kreira račun
        $month = (int) date('n', strtotime($invoice->getRawOriginal('issue_date')));
        
        KprEntry::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_amount,
            'month' => $month,
            'description' => 'Račun br. ' . $invoice->invoice_number . ' - ' . $invoice->customer->name,
        ]);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Ako se promijeni ukupni iznos, ažuriraj KPR zapis
        if ($invoice->isDirty('total_amount') && $invoice->kprEntry) {
            $month = (int) date('n', strtotime($invoice->getRawOriginal('issue_date')));
            
            $invoice->kprEntry->update([
                'amount' => $invoice->total_amount,
                'month' => $month,
                'description' => 'Račun br. ' . $invoice->invoice_number . ' - ' . $invoice->customer->name,
            ]);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        // Obriši povezani KPR zapis kada se obriše račun
        if ($invoice->kprEntry) {
            $invoice->kprEntry->delete();
        }
    }
}
