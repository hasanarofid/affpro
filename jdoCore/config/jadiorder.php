<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Store Settings
    |--------------------------------------------------------------------------
    |
    | These values serve as fallback defaults. All of them can be overridden
    | dynamically via the `settings` table in the database. Admin can change
    | store name, logo, colors, etc. from the admin panel.
    |
    */

    'store_name' => env('STORE_NAME', 'JadiOrder'),
    'store_logo' => null,
    'store_phone' => null,
    'store_email' => null,
    'store_address' => null,
    'store_description' => null,

    'primary_color' => '#4F46E5',
    'secondary_color' => '#7C3AED',
    'currency_symbol' => 'Rp',
    'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */

    'order_expiry_hours' => 24,
    'order_number_prefix' => 'JO',

    /*
    |--------------------------------------------------------------------------
    | Stock Settings
    |--------------------------------------------------------------------------
    */

    'min_stock_alert' => 5,

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | login_method: 'password', 'otp', 'both'
    | guest_checkout: Allow users to checkout without login
    |
    */

    'login_method' => 'password',
    'guest_checkout' => true,

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */

    'active_theme' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Queue & Shared Hosting
    |--------------------------------------------------------------------------
    |
    | Use database queue driver for shared hosting compatibility.
    | No Redis dependency required.
    |
    */

    'queue_connection' => env('QUEUE_CONNECTION', 'database'),

];
