<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('province_id')->nullable()->after('address_line');
            $table->string('city_id')->nullable()->after('province_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['province_id', 'city_id']);
        });
    }
};
