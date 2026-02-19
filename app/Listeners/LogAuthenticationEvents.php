<?php

namespace App\Listeners;

use App\Mail\AuthenticationNotification;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // Laravel log
        Log::info('User logged in', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
            'ip_address' => $logData['ip_address'] ?? null,
            'user_agent' => $logData['user_agent'] ?? null,
        ]);

        // Send admin notification
        $this->sendAdminNotification('login', $logData);
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

        // Laravel log
        Log::info('User logged out', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
            'ip_address' => $logData['ip_address'] ?? null,
            'user_agent' => $logData['user_agent'] ?? null,
        ]);

        // Send admin notification
        $this->sendAdminNotification('logout', $logData);
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

    /**
     * Send email notification to administrators
     */
    private function sendAdminNotification(string $eventType, array $logData): void
    {
        $adminEmails = config('app.admin_notification_emails', []);

        if (empty($adminEmails)) {
            Log::warning('Admin notification emails not configured - skipping email notification', [
                'event_type' => $eventType,
            ]);

            return;
        }

        // Prepare user data
        $userData = [
            'user_id' => $logData['user_id'] ?? null,
            'name' => $logData['name'] ?? 'N/A',
            'email' => $logData['email'] ?? 'N/A',
        ];

        // Prepare request data
        $requestData = [
            'ip_address' => $logData['ip_address'] ?? 'N/A',
            'user_agent' => $logData['user_agent'] ?? 'N/A',
            'timestamp' => $logData['timestamp'] ?? now(),
            'session_id' => $logData['session_id'] ?? null,
        ];

        Log::info('Sending admin notification emails', [
            'event_type' => $eventType,
            'admin_emails' => $adminEmails,
            'user' => $userData,
        ]);

        // Send email to all admin addresses
        $sentCount = 0;
        $failedCount = 0;

        foreach ($adminEmails as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(
                    new AuthenticationNotification($eventType, $userData, $requestData)
                );

                $sentCount++;

                Log::info('Admin notification email sent successfully', [
                    'event_type' => $eventType,
                    'recipient' => $adminEmail,
                    'user' => $userData['name'],
                ]);
            } catch (\Exception $e) {
                $failedCount++;

                Log::error('Failed to send admin notification email', [
                    'event_type' => $eventType,
                    'recipient' => $adminEmail,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Admin notification email batch completed', [
            'event_type' => $eventType,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'total' => count($adminEmails),
        ]);
    }
}
