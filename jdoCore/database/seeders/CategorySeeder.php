<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Kategori Pria', 'icon' => 'fa-mars'],
            ['name' => 'Kategori Wanita', 'icon' => 'fa-venus'],
            ['name' => 'Elektronik & Gadget', 'icon' => 'fa-laptop'],
            ['name' => 'Makanan & Minuman', 'icon' => 'fa-coffee'],
            ['name' => 'Kesehatan & Medis', 'icon' => 'fa-heartbeat'],
            ['name' => 'Perawatan & Kecantikan', 'icon' => 'fa-star'],
            ['name' => 'Kebutuhan Rumah Tangga', 'icon' => 'fa-home'],
            ['name' => 'Otomotif & Aksesoris', 'icon' => 'fa-car'],
            ['name' => 'Olahraga & Aktivitas Outdoor', 'icon' => 'fa-bicycle'],
            ['name' => 'Kebutuhan Ibu & Bayi', 'icon' => 'fa-baby-carriage'],
            ['name' => 'Buku, Alat Tulis & Hobi', 'icon' => 'fa-book'],
            ['name' => 'Tiket, Voucher & Tagihan', 'icon' => 'fa-ticket-alt'],
        ];

        foreach ($categories as $index => $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }
    }
}
