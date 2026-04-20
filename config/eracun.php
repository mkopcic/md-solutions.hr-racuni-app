<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FINA e-Račun Environment
    |--------------------------------------------------------------------------
    |
    | Okruženje: 'demo' ili 'production'
    | Demo okolina za testiranje, production za stvarnu razmjenu računa
    |
    */

    'environment' => env('ERACUN_ENVIRONMENT', 'demo'),

    /*
    |--------------------------------------------------------------------------
    | Demo Environment Configuration
    |--------------------------------------------------------------------------
    */

    'demo' => [
        'wsdl_url' => env('ERACUN_DEMO_URL') ?: null,
        'cert_path' => env('ERACUN_DEMO_CERT_PATH', storage_path('certificates/86058362621.A.4.pem')),
        'cert_password' => env('ERACUN_DEMO_CERT_PASSWORD'),
        'ca_cert_path' => env('ERACUN_DEMO_CA_CERT_PATH', storage_path('certificates/fina-demo-ca-root.pem')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Environment Configuration
    |--------------------------------------------------------------------------
    */

    'production' => [
        'wsdl_url' => 'https://eracun.fina.hr/axis2/services/SendB2BOutgoingInvoice?wsdl',
        'cert_path' => env('ERACUN_PROD_CERT_PATH', storage_path('certificates/eracun_production.p12')),
        'cert_password' => env('ERACUN_PROD_CERT_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supplier (Dobavljač) Information
    |--------------------------------------------------------------------------
    |
    | Podaci o dobavljaču (tvoj obrt/tvrtka)
    |
    */

    'supplier' => [
        'oib' => env('ERACUN_SUPPLIER_OIB'),
        'name' => env('ERACUN_SUPPLIER_NAME'),
        'address' => env('ERACUN_SUPPLIER_ADDRESS'),
        'city' => env('ERACUN_SUPPLIER_CITY'),
        'postal_code' => env('ERACUN_SUPPLIER_POSTAL_CODE'),
        'iban' => env('ERACUN_SUPPLIER_IBAN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('ERACUN_LOGGING_ENABLED', true),
        'channel' => env('ERACUN_LOG_CHANNEL', 'daily'),
        'log_xml' => env('ERACUN_LOG_XML', true), // Logiranje XML-a (dev only)
    ],

];
