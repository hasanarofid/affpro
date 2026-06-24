<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KiriminAja Module Configuration
    |--------------------------------------------------------------------------
    |
    | Semua konfigurasi KiriminAja disimpan di tabel `settings` (group: shipping)
    | dan dapat diubah dari Admin Panel > Pengaturan.
    |
    | Setting keys yang dipakai:
    |  - kiriminaja_api_key       (group: shipping)
    |  - kiriminaja_mode          (group: shipping) — staging | production
    |  - kiriminaja_origin_id     (group: shipping) — kecamatan_id asal
    |  - kiriminaja_origin_name   (group: shipping) — label tampilan
    |  - kiriminaja_couriers      (group: shipping) — JSON array kurir aktif
    |  - kiriminaja_callback_url  (group: shipping) — webhook callback (opsional)
    |
    | Catatan: shared key `enabled_couriers` & `origin_*` milik provider lama
    | (RajaOngkir) sengaja TIDAK dipakai di sini supaya kedua provider bisa
    | berdampingan tanpa saling menimpa.
    |
    */

    'endpoints' => [
        'staging'    => 'https://tdev.kiriminaja.com',
        'production' => 'https://client.kiriminaja.com',
    ],

    // Cache duration (seconds) untuk daftar destination/courier.
    'cache_ttl' => 3600,
];
