<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Email Settings'])]
class EmailSettings extends Component
{
    public string $mail_mailer = '';

    public string $mail_host = '';

    public string $mail_port = '';

    public ?string $mail_username = '';

    public ?string $mail_password = '';

    public string $mail_encryption = '';

    public string $mail_from_address = '';

    public string $mail_from_name = '';

    public bool $showSuccessMessage = false;

    // Test email properties
    public string $test_email = '';

    public string $test_subject = 'Test Email';

    public string $test_message = 'This is a test email from your application.';

    public ?string $testEmailSuccess = null;

    public ?string $testEmailError = null;

    /**
     * Mount the component and load current settings
     */
    public function mount(): void
    {
        $this->mail_mailer = config('mail.default') ?? env('MAIL_MAILER', 'smtp');
        $this->mail_host = config('mail.mailers.smtp.host') ?? env('MAIL_HOST', '');
        $this->mail_port = (string) (config('mail.mailers.smtp.port') ?? env('MAIL_PORT', '587'));
        $this->mail_username = config('mail.mailers.smtp.username') ?? env('MAIL_USERNAME', '');
        $this->mail_password = env('MAIL_PASSWORD', '');
        $this->mail_encryption = env('MAIL_ENCRYPTION', 'tls') ?? 'tls';
        $this->mail_from_address = config('mail.from.address') ?? env('MAIL_FROM_ADDRESS', '');
        $this->mail_from_name = config('mail.from.name') ?? env('MAIL_FROM_NAME', '');
    }

    /**
     * Update email settings in .env file
     */
    public function updateEmailSettings(): void
    {
        $this->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required|string|in:tls,ssl,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Update .env file
        $this->updateEnvFile([
            'MAIL_MAILER' => $this->mail_mailer,
            'MAIL_HOST' => $this->mail_host,
            'MAIL_PORT' => $this->mail_port,
            'MAIL_USERNAME' => $this->mail_username,
            'MAIL_PASSWORD' => $this->mail_password,
            'MAIL_ENCRYPTION' => $this->mail_encryption === 'none' ? '' : $this->mail_encryption,
            'MAIL_FROM_ADDRESS' => $this->mail_from_address,
            'MAIL_FROM_NAME' => $this->mail_from_name,
        ]);

        // Clear config cache
        Artisan::call('config:clear');

        $this->showSuccessMessage = true;
        $this->dispatch('email-settings-updated');
    }

    /**
     * Test email connection
     */
    public function testEmailConnection(): void
    {
        try {
            // Simple test to verify mail config loads
            config([
                'mail.mailers.smtp.host' => $this->mail_host,
                'mail.mailers.smtp.port' => $this->mail_port,
                'mail.mailers.smtp.username' => $this->mail_username,
                'mail.mailers.smtp.password' => $this->mail_password,
            ]);

            $this->dispatch('email-test-success', message: 'Email configuration looks valid. Save to test sending.');
        } catch (\Exception $e) {
            $this->dispatch('email-test-error', message: $e->getMessage());
        }
    }

    /**
     * Send a test email
     */
    public function sendTestEmail(): void
    {
        // Reset messages
        $this->testEmailSuccess = null;
        $this->testEmailError = null;

        $this->validate([
            'test_email' => 'required|email',
            'test_subject' => 'required|string|max:255',
            'test_message' => 'required|string',
        ]);

        // Check if SMTP is configured
        if (empty(config('mail.mailers.smtp.host')) || config('mail.mailers.smtp.host') === 'mailpit') {
            $this->testEmailError = 'SMTP settings are not configured. Please save your email settings first.';
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::raw($this->test_message, function ($message) {
                $message->to($this->test_email)
                    ->subject($this->test_subject);
            });

            $this->testEmailSuccess = 'Test email sent successfully to ' . $this->test_email . '! Check Mailpit at http://localhost:8025/';
        } catch (\Exception $e) {
            // Filter out debugbar measure errors (they don't affect mail sending)
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, "Failed stopping measure")) {
                $this->testEmailSuccess = 'Test email sent successfully to ' . $this->test_email . '! Check Mailpit at http://localhost:8025/';
            } else {
                $this->testEmailError = 'Failed to send: ' . $errorMessage;
            }
        }
    }

    /**
     * Update .env file with new values
     */
    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Escape special characters in value
            $value = $this->escapeEnvValue($value);

            // Check if key exists in .env
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key at the end
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * Escape special characters in env value
     */
    protected function escapeEnvValue(string $value): string
    {
        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/\s/', $value) || empty($value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}
