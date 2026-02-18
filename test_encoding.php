<?php

echo "=== ENCODING TEST ===\n\n";

// Direct hardcoded password
$passwordDirect = 'SuljoČepin31431??!!';
echo "Direct password: {$passwordDirect}\n";
echo 'Direct password length: '.strlen($passwordDirect)."\n";
echo 'Direct password hex: '.bin2hex($passwordDirect)."\n\n";

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// From .env
$passwordEnv = env('ERACUN_DEMO_CERT_PASSWORD');
echo "Env password: {$passwordEnv}\n";
echo 'Env password length: '.strlen($passwordEnv)."\n";
echo 'Env password hex: '.bin2hex($passwordEnv)."\n\n";

// Compare
if ($passwordDirect === $passwordEnv) {
    echo "✅ Passwords MATCH\n";
} else {
    echo "❌ Passwords DO NOT MATCH\n";
    echo "Difference in hex:\n";
    echo '  Direct: '.bin2hex($passwordDirect)."\n";
    echo '  Env:    '.bin2hex($passwordEnv)."\n";
}

echo "\n=== CERTIFICATE TEST ===\n\n";

$certPath = 'C:/laragon/www/obrt-racuni-laravel-app/storage/certificates/86058362621.A.4.p12';
$certData = file_get_contents($certPath);

echo "Testing with DIRECT password...\n";
$certs = [];
if (openssl_pkcs12_read($certData, $certs, $passwordDirect)) {
    echo "✅ SUCCESS with direct password!\n";
} else {
    echo '❌ FAILED with direct password: '.openssl_error_string()."\n";
}

echo "\nTesting with ENV password...\n";
$certs = [];
while (openssl_error_string() !== false); // Clear errors
if (openssl_pkcs12_read($certData, $certs, $passwordEnv)) {
    echo "✅ SUCCESS with env password!\n";
} else {
    echo '❌ FAILED with env password: '.openssl_error_string()."\n";
}
