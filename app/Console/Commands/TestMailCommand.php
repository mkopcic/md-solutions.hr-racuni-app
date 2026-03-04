<?php

namespace App\Console\Commands;

use App\Mail\MailServerStatusReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email? : Email adresa primatelja}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pošalji test email s mail server status izvještajem';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'md-solutions@md-solutions.hr';
        
        $this->info('📧 Slanje test email-a...');
        $this->newLine();
        
        $this->info('Primatelj: ' . $email);
        $this->info('Predmet: 📧 Mail Server Status Report - md-solutions.hr');
        $this->info('Attachment: MAIL-SERVER-STATUS.md');
        $this->newLine();
        
        try {
            Mail::to($email)->send(new MailServerStatusReport($email));
            
            $this->components->success('✅ Email uspješno poslan na: ' . $email);
            $this->newLine();
            
            $this->components->info('Provjeri inbox (i spam folder) na primateljevoj adresi.');
            $this->newLine();
            
            $this->table(
                ['Postavka', 'Vrijednost'],
                [
                    ['MAIL_MAILER', config('mail.default')],
                    ['MAIL_HOST', config('mail.mailers.smtp.host')],
                    ['MAIL_PORT', config('mail.mailers.smtp.port')],
                    ['MAIL_FROM', config('mail.from.address')],
                ]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->components->error('❌ Greška pri slanju emaila:');
            $this->error($e->getMessage());
            $this->newLine();
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
