<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Level 1: Variant types (e.g., "Warna", "Ukuran")
        Schema::create('product_variant_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name', 100);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Level 2: Variant values (e.g., "Merah", "Biru", "L", "XL")
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_type_id')->constrained('product_variant_types')->cascadeOnDelete();
            $table->string('value', 100);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Level 3: Variant combinations with price/stock (e.g., "Merah-L", "Merah-XL")
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->integer('weight')->nullable()->comment('Override weight if different');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot: Maps variant to its value combination
        Schema::create('product_variant_combinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('variant_value_id')->constrained('product_variant_values')->cascadeOnDelete();

            $table->unique(['product_variant_id', 'variant_value_id'], 'pvc_variant_value_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_combinations');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variant_types');
    }
};
