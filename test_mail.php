<?php
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Mail::raw('This is a test email', function ($message) {
        $message->to('test@example.com')
                ->subject('Test Email from CLI');
    });
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
