<?php

/**
 * Ekstraktuje e-Račun certifikat iz .p12 u .pem format
 *
 * VAŽNO: Certifikat koristi legacy RC2/3DES enkripciju koja zahtijeva
 * da PHP/OpenSSL ima omogućen legacy provider.
 *
 * Za Windows OpenSSL 3.x:
 * - Legacy provider: C:\Program Files\OpenSSL-Win64\bin\legacy.dll
 * - Potrebno je postaviti OPENSSL_MODULES environment varijablu
 *
 * Više detalji: docs/OPENSSL_LEGACY_PROVIDER.md
 */

$p12Path = __DIR__.'/storage/certificates/86058362621.A.4.p12';
$certOutPath = __DIR__.'/storage/certificates/86058362621.A.4-cert.pem';
$keyOutPath = __DIR__.'/storage/certificates/86058362621.A.4-key.pem';
$combinedOutPath = __DIR__.'/storage/certificates/86058362621.A.4.pem';

// Validna lozinka (26.02.2026)
$password = 'K2wbNnwGuFT4X9';

echo "🔍 Ekstraktujem e-Račun certifikat iz .p12...\n\n";
echo "Certifikat: $p12Path\n";
echo 'Veličina: '.filesize($p12Path)." bytes\n";
echo "Password: $password\n\n";

// VAŽNO: Certifikat koristi legacy RC2/3DES enkripciju
// Koristimo OpenSSL CLI sa legacy providerom jer PHP openssl_pkcs12_read() ne podržava

// Provjeri postoji li OpenSSL
$opensslPath = 'C:\Program Files\OpenSSL-Win64\bin\openssl.exe';
if (!file_exists($opensslPath)) {
    echo "❌ OpenSSL nije pronađen na: $opensslPath\n";
    echo "Instaliraj OpenSSL sa: https://slproweb.com/products/Win32OpenSSL.html\n";
    exit(1);
}

// Provjeri legacy provider
$legacyPath = 'C:\Program Files\OpenSSL-Win64\bin\legacy.dll';
if (!file_exists($legacyPath)) {
    echo "❌ Legacy provider nije pronađen: $legacyPath\n";
    echo "Instaliraj punu verziju OpenSSL-a (ne Light verziju)\n";
    exit(1);
}

echo "✅ OpenSSL pronađen: $opensslPath\n";
echo "✅ Legacy provider pronađen: $legacyPath\n\n";

// Postavi OPENSSL_MODULES environment varijablu
putenv('OPENSSL_MODULES=C:\Program Files\OpenSSL-Win64\bin');

// 1. Ekstraktuj kombinirani PEM (cert + key)
echo "📦 Ekstraktujem kombinirani PEM...\n";
$cmd = sprintf(
    '"%s" pkcs12 -provider legacy -provider default -in "%s" -out "%s" -nodes -passin "pass:%s" 2>&1',
    $opensslPath,
    $p12Path,
    $combinedOutPath,
    $password
);
exec($cmd, $output, $returnCode);
if ($returnCode !== 0) {
    echo "❌ Greška pri ekstraktovanju kombiniranog PEM-a:\n";
    echo implode("\n", $output)."\n";
    exit(1);
}
echo "✅ Kombinirani PEM: $combinedOutPath\n";

// 2. Ekstraktuj samo certifikat
echo "📦 Ekstraktujem certifikat...\n";
$cmd = sprintf(
    '"%s" pkcs12 -provider legacy -provider default -in "%s" -clcerts -nokeys -out "%s" -passin "pass:%s" 2>&1',
    $opensslPath,
    $p12Path,
    $certOutPath,
    $password
);
exec($cmd, $output, $returnCode);
if ($returnCode !== 0) {
    echo "❌ Greška pri ekstraktovanju certifikata:\n";
    echo implode("\n", $output)."\n";
    exit(1);
}
echo "✅ Certifikat: $certOutPath\n";

// 3. Ekstraktuj samo private key
echo "📦 Ekstraktujem private key...\n";
$cmd = sprintf(
    '"%s" pkcs12 -provider legacy -provider default -in "%s" -nocerts -nodes -out "%s" -passin "pass:%s" 2>&1',
    $opensslPath,
    $p12Path,
    $keyOutPath,
    $password
);
exec($cmd, $output, $returnCode);
if ($returnCode !== 0) {
    echo "❌ Greška pri ekstraktovanju private key-a:\n";
    echo implode("\n", $output)."\n";
    exit(1);
}
echo "✅ Private key: $keyOutPath\n\n";

// 4. Validacija: Prikaži informacije o certifikatu
echo "📋 INFORMACIJE O CERTIFIKATU:\n";
$cmd = sprintf('"%s" x509 -in "%s" -noout -subject -issuer -dates 2>&1', $opensslPath, $certOutPath);
exec($cmd, $output, $returnCode);
if ($returnCode === 0) {
    echo implode("\n", $output)."\n\n";
} else {
    echo "❌ Greška pri čitanju certifikata informacija\n\n";
}

// 5. Validacija: SHA1 Fingerprint
$output = [];
$cmd = sprintf('"%s" x509 -in "%s" -noout -fingerprint -sha1 2>&1', $opensslPath, $certOutPath);
exec($cmd, $output, $returnCode);
if ($returnCode === 0) {
    echo "🔑 ".implode("\n", $output)."\n\n";
}

// 6. Validacija: Provjeri da li cert i key pripadaju jedan drugom
echo "🔍 VALIDACIJA CERT/KEY PARA:\n";
$output = [];
$cmd = sprintf('"%s" x509 -in "%s" -noout -modulus 2>&1', $opensslPath, $certOutPath);
exec($cmd, $certModulus, $returnCode1);
$cmd = sprintf('"%s" rsa -in "%s" -noout -modulus 2>&1', $opensslPath, $keyOutPath);
exec($cmd, $keyModulus, $returnCode2);

if ($returnCode1 === 0 && $returnCode2 === 0) {
    $certMod = md5($certModulus[0]);
    $keyMod = md5($keyModulus[0]);

    if ($certMod === $keyMod) {
        echo "✅ Certifikat i key SU VALJANI PAR (moduli se poklapaju)\n";
        echo "   Cert modulus MD5: $certMod\n";
        echo "   Key modulus MD5:  $keyMod\n\n";
    } else {
        echo "❌ Certifikat i key NISU VALJANI PAR!\n";
        echo "   Cert modulus MD5: $certMod\n";
        echo "   Key modulus MD5:  $keyMod\n\n";
        exit(1);
    }
}

echo "✅ USPJEŠNO EKSTRAKTOVANI FAJLOVI:\n";
echo "   1. $combinedOutPath (kombinirani - za Laravel/Guzzle)\n";
echo "   2. $certOutPath (samo certifikat)\n";
echo "   3. $keyOutPath (samo private key)\n\n";

echo "🎉 Sada možeš pokrenuti: php artisan eracun:test diagnostics\n";
