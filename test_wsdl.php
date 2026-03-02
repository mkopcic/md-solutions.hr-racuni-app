<?php

require __DIR__.'/vendor/autoload.php';

use Illuminate\Http\Client\Factory as HttpFactory;

$url = 'https://cistest.apis-it.hr:8449/EracunServiceTest?wsdl';
$certPath = __DIR__.'/storage/certificates/86058362621.A.4.pem';
$caPath = __DIR__.'/storage/certificates/fina-demo-ca-root.pem';

echo "Fetching: {$url}\n";
echo "Cert: {$certPath}\n";
echo "CA: {$caPath}\n\n";

$http = new HttpFactory();

try {
    $response = $http->withOptions([
        'cert' => $certPath,
        'verify' => false, // Privremeno disable SSL verification
    ])->get($url);

    if ($response->successful()) {
        echo "✅ SUCCESS!\n\n";
        $wsdl = $response->body();

        // Spremi u fajl
        $outputPath = __DIR__.'/storage/app/EracunServiceTest.wsdl';
        file_put_contents($outputPath, $wsdl);

        echo "WSDL spremljen: {$outputPath}\n\n";
        echo "Prvih 500 karaktera:\n";
        echo substr($wsdl, 0, 500);
    } else {
        echo "❌ HTTP Error: ".$response->status()."\n";
        echo $response->body();
    }
} catch (Exception $e) {
    echo "❌ Exception: ".$e->getMessage()."\n";
}
