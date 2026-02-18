<?php

/**
 * Ekstraktuje certifikat i ključ iz .p12 u zasebne .pem fajlove
 * Koristim pristup iz fiskalizacija/fiskal_fix.md
 */
$p12Path = __DIR__.'/storage/certificates/86058362621.A.4.p12';
$certOutPath = __DIR__.'/storage/certificates/86058362621.A.4-cert.pem';
$keyOutPath = __DIR__.'/storage/certificates/86058362621.A.4-key.pem';
$combinedOutPath = __DIR__.'/storage/certificates/86058362621.A.4.pem';

// Pokušaj različite passworde
$passwords = [
    'SuljoČepin31431??!!',
    'u0PEzhMBCY8a4zaY',
    'Suljočepin31431??!!',
    'SuljoČepin31431!!??',
    '',
];

echo "🔍 Pokušavam ekstraktovati certifikat iz .p12...\n\n";
echo "Fajl: $p12Path\n";
echo 'Veličina: '.filesize($p12Path)." bytes\n\n";

$p12Content = file_get_contents($p12Path);

foreach ($passwords as $index => $password) {
    echo 'Pokušaj '.($index + 1).': ';
    echo 'Password length: '.strlen($password).' chars';
    echo ' | Hex: '.bin2hex(substr($password, 0, 15))."...\n";

    $certs = [];
    if (openssl_pkcs12_read($p12Content, $certs, $password)) {
        echo "✅ SUCCESS! Password radi!\n\n";
        echo "Password koji radi: $password\n";

        // Snimi certifikat
        file_put_contents($certOutPath, $certs['cert']);
        echo "✅ Certifikat snimljen: $certOutPath\n";

        // Snimi privatni ključ
        file_put_contents($keyOutPath, $certs['pkey']);
        echo "✅ Private key snimljen: $keyOutPath\n";

        // Snimi kombinirani (za curl/Guzzle)
        file_put_contents($combinedOutPath, $certs['cert']."\n".$certs['pkey']);
        echo "✅ Kombinirani PEM snimljen: $combinedOutPath\n\n";

        // Provjeri fingerprint
        $certResource = openssl_x509_read($certs['cert']);
        if ($certResource) {
            $certInfo = openssl_x509_parse($certResource);
            echo "📋 Certifikat info:\n";
            echo '  Subject: '.json_encode($certInfo['subject'])."\n";
            echo '  Issuer: '.json_encode($certInfo['issuer'])."\n";
            echo '  Valid od: '.date('Y-m-d H:i:s', $certInfo['validFrom_time_t'])."\n";
            echo '  Valid do: '.date('Y-m-d H:i:s', $certInfo['validTo_time_t'])."\n";

            // Izračunaj SHA1 fingerprint kao u fiskal_fix.md
            $fingerprint = openssl_x509_fingerprint($certResource, 'sha1');
            echo "  SHA1 Fingerprint: $fingerprint\n";
        }

        exit(0);
    } else {
        echo '❌ FAILED: '.openssl_error_string()."\n";
        // Clear error queue
        while (openssl_error_string() !== false);
    }
}

echo "\n❌ Nijedan password ne radi!\n";
echo "Trebaš kontaktirati FINA za:\n";
echo "  1. Ispravan password za certifikat 86058362621.A.4.p12\n";
echo "  2. Ili novi certifikat sa passwordom\n";
echo "  3. Ili direktno .pem fajlove umjesto .p12\n";
