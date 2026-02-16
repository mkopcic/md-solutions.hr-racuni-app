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
            'description' => 'Račun br. '.$invoice->id.' - '.$invoice->customer->name,
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
                'description' => 'Račun br. '.$invoice->id.' - '.$invoice->customer->name,
            ]);
        }

        // Automatski ažuriraj status na osnovu plaćanja
        if ($invoice->isDirty(['paid_cash', 'paid_transfer', 'total_amount'])) {
            $totalPaid = $invoice->paid_cash + $invoice->paid_transfer;

            if ($totalPaid >= $invoice->total_amount && $totalPaid > 0) {
                // Račun je u potpunosti plaćen
                if ($invoice->status !== 'paid') {
                    $invoice->status = 'paid';
                    $invoice->payment_date = $invoice->payment_date ?? now();
                    $invoice->saveQuietly(); // Koristi saveQuietly da se izbjegne beskonačna petlja
                }
            } elseif ($totalPaid > 0 && $totalPaid < $invoice->total_amount) {
                // Račun je djelomično plaćen
                if ($invoice->status !== 'partial') {
                    $invoice->status = 'partial';
                    $invoice->saveQuietly();
                }
            } elseif ($totalPaid == 0) {
                // Račun nije plaćen
                if ($invoice->status !== 'unpaid') {
                    $invoice->status = 'unpaid';
                    $invoice->payment_date = null;
                    $invoice->saveQuietly();
                }
            }
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
