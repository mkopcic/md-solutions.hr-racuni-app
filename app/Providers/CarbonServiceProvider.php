<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class CarbonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Postavlja Carbon na hrvatski jezik
        Carbon::setLocale(config('app.locale'));

        // Alternativno, možete eksplicitno postaviti 'hr'
        // Carbon::setLocale('hr');

        setlocale(LC_TIME, 'hr_HR.utf8', 'hr_HR', 'hr', 'croatian');
    }
}
