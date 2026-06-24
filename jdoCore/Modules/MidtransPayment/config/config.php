<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Module Configuration
    |--------------------------------------------------------------------------
    |
    | Semua konfigurasi Midtrans disimpan di tabel Settings
    | dan dapat diubah melalui Admin Panel > Pengaturan.
    |
    | Setting keys (group: payment):
    |  - midtrans_server_key      — Server key (untuk charge & verifikasi)
    |  - midtrans_client_key      — Client key (untuk Snap.js frontend)
    |  - midtrans_mode            — "sandbox" atau "production"
    |  - midtrans_enabled_channels — JSON array channel aktif
    |
    */

    // Midtrans API base URLs
    'base_url' => [
        'sandbox' => 'https://app.sandbox.midtrans.com',
        'production' => 'https://app.midtrans.com',
    ],

    'snap_url' => [
        'sandbox' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
        'production' => 'https://app.midtrans.com/snap/v1/transactions',
    ],

    'snap_js' => [
        'sandbox' => 'https://app.sandbox.midtrans.com/snap/snap.js',
        'production' => 'https://app.midtrans.com/snap/snap.js',
    ],

    // Default transaction expiry in minutes
    'expiry_duration' => 1440, // 24 hours

    // Default currency
    'currency' => 'IDR',
];
