<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RajaOngkir Module Configuration
    |--------------------------------------------------------------------------
    |
    | Semua konfigurasi RajaOngkir disimpan di tabel Settings
    | dan dapat diubah melalui Admin Panel > Pengaturan.
    |
    | Setting keys:
    |  - rajaongkir_api_key  (group: shipping)
    |  - rajaongkir_type     (group: shipping) — starter|basic|pro
    |  - origin_city_id      (group: shipping) — ID kota asal
    |  - enabled_couriers    (group: shipping) — JSON array, e.g. ["jne","pos","tiki"]
    |
    */

    // Base URLs per account type (hardcoded, no need to configure)
    'base_url' => [
        'starter' => 'https://api.rajaongkir.com/starter',
        'basic' => 'https://api.rajaongkir.com/basic',
        'pro' => 'https://pro.rajaongkir.com/api',
    ],

    // Cache duration in hours for province/city data
    'cache_hours' => 720, // 30 days
];
