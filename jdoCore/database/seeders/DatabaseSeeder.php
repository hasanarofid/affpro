<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        // Create default superadmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@jadiorder.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@jadiorder.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Seed default settings
        $settings = [
            // General
            ['group' => 'general', 'key' => 'store_name', 'value' => 'JadiOrder', 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_logo', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_phone', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_email', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_address', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_description', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'primary_color', 'value' => '#4F46E5', 'type' => 'string'],
            ['group' => 'general', 'key' => 'secondary_color', 'value' => '#7C3AED', 'type' => 'string'],
            ['group' => 'general', 'key' => 'theme_primary_palette', 'value' => 'indigo', 'type' => 'string'],
            ['group' => 'general', 'key' => 'theme_secondary_palette', 'value' => 'purple', 'type' => 'string'],
            ['group' => 'general', 'key' => 'currency_symbol', 'value' => 'Rp', 'type' => 'string'],
            ['group' => 'general', 'key' => 'locale', 'value' => 'id', 'type' => 'string'],

            // Store
            ['group' => 'store', 'key' => 'order_expiry_hours', 'value' => '24', 'type' => 'int'],
            ['group' => 'store', 'key' => 'min_stock_alert', 'value' => '5', 'type' => 'int'],
            ['group' => 'store', 'key' => 'login_method', 'value' => 'password', 'type' => 'string'],
            ['group' => 'store', 'key' => 'guest_checkout', 'value' => 'true', 'type' => 'bool'],
            ['group' => 'store', 'key' => 'active_theme', 'value' => 'default', 'type' => 'string'],

            // Payment
            [
                'group' => 'payment',
                'key' => 'bank_accounts',
                'value' => json_encode([
                    ['bank' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Toko JadiOrder'],
                ]),
                'type' => 'json'
            ],

            // Shipping
            ['group' => 'shipping', 'key' => 'active_courier_provider', 'value' => 'rajaongkir', 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'origin_city_id', 'value' => null, 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'rajaongkir_api_key', 'value' => null, 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'rajaongkir_type', 'value' => 'starter', 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'enabled_couriers', 'value' => json_encode(['jne', 'pos', 'tiki']), 'type' => 'json'],

            // KiriminAja
            ['group' => 'shipping', 'key' => 'kiriminaja_api_key', 'value' => null, 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'kiriminaja_mode', 'value' => 'staging', 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'kiriminaja_origin_id', 'value' => null, 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'kiriminaja_origin_name', 'value' => null, 'type' => 'string'],
            ['group' => 'shipping', 'key' => 'kiriminaja_couriers', 'value' => json_encode(['jne', 'jnt', 'sicepat', 'anteraja', 'ide']), 'type' => 'json'],
            ['group' => 'shipping', 'key' => 'kiriminaja_callback_url', 'value' => null, 'type' => 'string'],

            // WhatsApp
            ['group' => 'whatsapp', 'key' => 'wa_api_url', 'value' => null, 'type' => 'string'],
            ['group' => 'whatsapp', 'key' => 'wa_api_key', 'value' => null, 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Run dummy data seeder
        $this->call(DummyDataSeeder::class);
    }
}
