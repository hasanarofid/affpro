<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');                  // path uploaded image
            $table->enum('link_type', ['none', 'product', 'page', 'url'])->default('none');
            $table->string('link_target', 500)->nullable(); // product slug | page slug | absolute url
            $table->string('button_label', 100)->nullable(); // optional CTA text
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedSmallInteger('display_delay')->default(0); // seconds before showing
            $table->boolean('show_once_per_session')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_popups');
    }
};
