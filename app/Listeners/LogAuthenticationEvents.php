<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;

class LogAuthenticationEvents
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $this->logAuthEvent($event);
    }

    /**
     * Log authentication events
     */
    private function logAuthEvent(object $event): void
    {
        $eventClass = class_basename($event);
        $logData = $this->prepareLogData($event);

        switch ($eventClass) {
            case 'Login':
                $this->logLogin($event, $logData);
                break;
            case 'Logout':
                $this->logLogout($event, $logData);
                break;
            case 'Registered':
                $this->logRegistration($event, $logData);
                break;
            case 'Failed':
                $this->logFailedLogin($event, $logData);
                break;
            case 'Lockout':
                $this->logLockout($event, $logData);
                break;
            case 'Attempting':
                $this->logLoginAttempt($event, $logData);
                break;
        }
    }

    /**
     * Prepare common log data
     */
    private function prepareLogData(object $event): array
    {
        $data = [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ];

        // Add session ID if session is available (not in console)
        if (request()->hasSession()) {
            $data['session_id'] = request()->session()->getId();
        }

        return $data;
    }

    /**
     * Log successful login
     */
    private function logLogin(Login $event, array $logData): void
    {
        $logData = array_merge($logData, [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
            'remember' => property_exists($event, 'remember') ? $event->remember : false,
        ]);

        activity('authentication')
            ->causedBy($event->user)
            ->withProperties($logData)
            ->log("Korisnik {$event->user->name} se uspješno prijavio");
    }

    /**
     * Log logout
     */
    private function logLogout(Logout $event, array $logData): void
    {
        $logData = array_merge($logData, [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
        ]);

        activity('authentication')
            ->causedBy($event->user)
            ->withProperties($logData)
            ->log("Korisnik {$event->user->name} se odjavio");
    }

    /**
     * Log user registration
     */
    private function logRegistration(Registered $event, array $logData): void
    {
        $logData = array_merge($logData, [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
        ]);

        activity('authentication')
            ->causedBy($event->user)
            ->withProperties($logData)
            ->log("Nova registracija korisnika: {$event->user->name}");
    }

    /**
     * Log failed login attempt
     */
    private function logFailedLogin(Failed $event, array $logData): void
    {
        $credentials = $event->credentials;
        $email = $credentials['email'] ?? 'nepoznat';

        $logData = array_merge($logData, [
            'email' => $email,
            'attempted_email' => $email,
        ]);

        activity('authentication')
            ->withProperties($logData)
            ->log("Neuspješna prijava za email: {$email}");
    }

    /**
     * Log account lockout
     */
    private function logLockout(Lockout $event, array $logData): void
    {
        activity('authentication')
            ->withProperties($logData)
            ->log('Račun je zaključan zbog previše neuspješnih pokušaja prijave');
    }

    /**
     * Log login attempt
     */
    private function logLoginAttempt(Attempting $event, array $logData): void
    {
        $credentials = $event->credentials;
        $email = $credentials['email'] ?? 'nepoznat';

        $logData = array_merge($logData, [
            'email' => $email,
            'remember' => $event->remember ?? false,
        ]);

        activity('authentication')
            ->withProperties($logData)
            ->log("Pokušaj prijave za email: {$email}");
    }
}
