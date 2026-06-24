<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit Payment Module Configuration
    |--------------------------------------------------------------------------
    |
    | Semua konfigurasi Xendit disimpan di tabel Settings
    | dan dapat diubah melalui Admin Panel > Pengaturan.
    |
    | Setting keys (group: payment):
    |  - xendit_secret_key        — Secret API key (server key)
    |  - xendit_public_key        — Public API key (optional, not used currently)
    |  - xendit_callback_token    — Callback verification token from Xendit Dashboard
    |  - xendit_mode              — "sandbox" or "live"
    |  - xendit_enabled_channels  — JSON array of enabled channels, e.g. ["BCA","MANDIRI","QRIS","OVO"]
    |
    */

    // Xendit API base URL (production). Sandbox uses the same URL but with test keys.
    'base_url' => 'https://api.xendit.co',

    // Invoice duration in seconds (default: 24 hours)
    'invoice_duration' => 86400,

    // Default currency
    'currency' => 'IDR',
];
