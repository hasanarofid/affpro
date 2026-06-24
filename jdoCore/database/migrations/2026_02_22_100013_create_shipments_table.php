<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('courier_code', 50);
            $table->string('courier_service', 100);
            $table->string('tracking_number')->nullable();
            $table->decimal('shipping_cost', 15, 2);
            $table->integer('estimated_days')->nullable();
            $table->string('status', 50)->default('pending');
            $table->json('tracking_history')->nullable();
            $table->string('delivered_photo', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
