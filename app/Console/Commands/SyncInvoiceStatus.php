<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class SyncInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronizira status računa na temelju plaćenih iznosa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Sinkronizacija statusa računa...');

        $invoices = Invoice::all();
        $updated = 0;

        foreach ($invoices as $invoice) {
            $oldStatus = $invoice->status;
            $invoice->updateStatus();

            if ($oldStatus !== $invoice->status) {
                $updated++;
                $this->line("Račun #{$invoice->id}: {$oldStatus} → {$invoice->status}");
            }
        }

        $this->info("Gotovo! Ažurirano {$updated} od {$invoices->count()} računa.");

        return self::SUCCESS;
    }
}
