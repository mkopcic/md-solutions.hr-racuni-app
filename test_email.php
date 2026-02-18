<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    echo "Šaljem test email...\n";

    Mail::raw('Test email from Laravel', function ($message) {
        $message->to('test@test.com')
                ->subject('Test Email');
    });

    echo "✅ Email poslan uspješno!\n";
    echo "Provjeri Mailpit: http://localhost:8025/\n";
} catch (\Exception $e) {
    echo "❌ Greška: " . $e->getMessage() . "\n";
    echo "Tip greške: " . get_class($e) . "\n";
}
