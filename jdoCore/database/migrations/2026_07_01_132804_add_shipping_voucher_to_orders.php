<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_discount_amount', 15, 2)->default(0)->after('discount_amount');
            $table->foreignId('shipping_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete()->after('voucher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_voucher_id']);
            $table->dropColumn('shipping_voucher_id');
            $table->dropColumn('shipping_discount_amount');
        });
    }
};
