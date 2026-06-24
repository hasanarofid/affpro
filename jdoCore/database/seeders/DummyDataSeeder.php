<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Setting;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Default Categories
        $categories = [
            ['name' => 'Pakaian Pria', 'icon' => 'bi-gender-male'],
            ['name' => 'Pakaian Wanita', 'icon' => 'bi-gender-female'],
            ['name' => 'Elektronik', 'icon' => 'bi-laptop'],
            ['name' => 'Aksesoris', 'icon' => 'bi-watch'],
            ['name' => 'Kecantikan', 'icon' => 'bi-magic'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Setup Dummy Products
        $pakaianPria = Category::where('slug', 'pakaian-pria')->first();
        $elektronik = Category::where('slug', 'elektronik')->first();

        if ($pakaianPria) {
            Product::firstOrCreate(
                ['slug' => 'kaos-polos-premium'],
                [
                    'category_id' => $pakaianPria->id,
                    'name' => 'Kaos Polos Premium',
                    'description' => 'Kaos polos dengan bahan cotton combed 30s yang sangat nyaman dipakai.',
                    'base_price' => 50000,
                    'stock' => 100,
                    'weight' => 200,
                    'is_active' => true,
                ]
            );
        }

        if ($elektronik) {
            Product::firstOrCreate(
                ['slug' => 'earphone-tws-bluetooth'],
                [
                    'category_id' => $elektronik->id,
                    'name' => 'Earphone TWS Bluetooth',
                    'description' => 'TWS Bluetooth 5.0 dengan kualitas suara jernih dan bass yang mantap.',
                    'base_price' => 150000,
                    'stock' => 50,
                    'weight' => 150,
                    'is_active' => true,
                ]
            );
        }

        // 3. Setup Dummy Blog Post
        $adminUserId = \App\Models\User::first() ? \App\Models\User::first()->id : 1;
        BlogPost::firstOrCreate(
            ['slug' => 'selamat-datang-di-toko-kami'],
            [
                'title' => 'Selamat Datang di Toko Kami',
                'content' => 'Halo pelanggan setia! Kami berkomitmen memberikan layanan terbaik dengan produk berkualitas. Belanja sekarang dan dapatkan diskon menarik di toko online baru kami.',
                'is_published' => true,
                'published_at' => now(),
                'author_id' => $adminUserId,
            ]
        );

        // 4. Optionally Set default logo if needed
        // Assuming user might upload later, but setting a placeholder just in case
        $logoSetting = Setting::where('key', 'store_logo')->first();
        if (!$logoSetting || blank($logoSetting->value)) {
            Setting::updateOrCreate(
                ['key' => 'store_logo', 'group' => 'general'],
                ['value' => 'assets/images/default-logo.png', 'type' => 'string']
            );
        }
    }
}
