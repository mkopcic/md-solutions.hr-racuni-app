<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $quote = Inspiring::quote();
    $this->comment($quote);
    
    Mail::raw($quote, function ($message) {
        $message->to('mkopcic@gmail.com')
                ->subject('Inspiring Quote - ' . now()->format('d.m.Y H:i'));
    });
    
    $this->info('Quote sent to mkopcic@gmail.com');
})->purpose('Display an inspiring quote');

// Backup schedule
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');

// Queue worker - runs every minute, stops when queue is empty
Schedule::command('queue:work --stop-when-empty')->everyMinute();

// Inspiring quote - every 6 hours to mkopcic@gmail.com
Schedule::command('inspire')->everySixHours();
