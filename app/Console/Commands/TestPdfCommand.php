<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class TestPdfCommand extends Command
{
    protected $signature = 'test:pdf';

    protected $description = 'Test PDF generation';

    public function handle()
    {
        try {
            $invoice = Invoice::first();
            $business = Business::first();

            if (! $invoice || ! $business) {
                $this->error('No invoice or business found!');

                return;
            }

            $this->info('Testing PDF generation...');

            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'business' => $business,
            ]);

            $output = $pdf->output();

            if ($output) {
                $this->info('PDF generated successfully! Size: '.strlen($output).' bytes');

                // Save to storage for testing
                file_put_contents(storage_path('app/test-invoice.pdf'), $output);
                $this->info('PDF saved to: '.storage_path('app/test-invoice.pdf'));
            } else {
                $this->error('PDF generation failed - empty output');
            }
        } catch (\Exception $e) {
            $this->error('PDF generation failed: '.$e->getMessage());
            $this->error('File: '.$e->getFile());
            $this->error('Line: '.$e->getLine());
        }
    }
}
