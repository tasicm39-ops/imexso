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
        Schema::create('car_marketings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->boolean('limited_stock_enabled')->default(false);
            $table->unsignedInteger('limited_stock_count')->nullable();
            $table->boolean('new_price_enabled')->default(false);
            $table->decimal('new_price_amount', 12, 2)->nullable();
            $table->boolean('promotion_enabled')->default(false);
            $table->string('promotion_label')->nullable();
            $table->string('badge_text', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_marketings');
    }
};
