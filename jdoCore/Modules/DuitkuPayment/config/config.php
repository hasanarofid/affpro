<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duitku Payment Module Configuration
    |--------------------------------------------------------------------------
    |
    | Semua konfigurasi Duitku disimpan di tabel Settings
    | dan dapat diubah melalui Admin Panel > Pengaturan.
    |
    | Setting keys (group: payment):
    |  - duitku_merchant_code      — Merchant code dari dashboard Duitku
    |  - duitku_merchant_key       — Merchant/API key (secret)
    |  - duitku_mode               — "sandbox" atau "production"
    |  - duitku_enabled_channels   — JSON array channel aktif
    |
    */

    // Duitku Pop API URLs
    'create_invoice_url' => [
        'sandbox' => 'https://api-sandbox.duitku.com/api/merchant/createInvoice',
        'production' => 'https://api-prod.duitku.com/api/merchant/createInvoice',
    ],

    'get_payment_method_url' => [
        'sandbox' => 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod',
        'production' => 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod',
    ],

    'check_transaction_url' => [
        'sandbox' => 'https://api-sandbox.duitku.com/api/merchant/transactionStatus',
        'production' => 'https://api-prod.duitku.com/api/merchant/transactionStatus',
    ],

    // Default transaction expiry in minutes
    'expiry_period' => 1440, // 24 hours

    // Default currency
    'currency' => 'IDR',
];
