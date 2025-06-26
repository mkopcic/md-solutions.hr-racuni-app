<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Attempting;
use App\Listeners\LogAuthenticationEvents;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registracija Invoice observera
        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);

        // Registracija authentication event listenera
        $authEvents = [
            Login::class,
            Logout::class,
            Registered::class,
            Failed::class,
            Lockout::class,
            Attempting::class,
        ];

        foreach ($authEvents as $event) {
            Event::listen($event, LogAuthenticationEvents::class);
        }
    }
}
