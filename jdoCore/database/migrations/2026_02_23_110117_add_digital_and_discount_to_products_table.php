<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount_price', 15, 2)->after('base_price')->nullable();
            $table->integer('min_order')->after('discount_price')->default(1);
            $table->integer('sold_count')->after('stock')->default(0);
            $table->enum('type', ['physical', 'digital'])->after('description')->default('physical');
            $table->text('digital_info_text')->after('type')->nullable();
            $table->string('digital_file_path')->after('digital_info_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount_price', 'sold_count', 'min_order', 'type', 'digital_info_text', 'digital_file_path']);
        });
    }
};
