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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('margin_type')->nullable();
            $table->decimal('margin_amount', 10, 2)->nullable();
            $table->decimal('vat_rate', 5, 2)->default(20);
            $table->unsignedSmallInteger('validity_days')->nullable();
            $table->decimal('price_excl_vat', 12, 2);
            $table->decimal('price_incl_vat', 12, 2);
            $table->string('client_name');
            $table->string('client_email');
            $table->text('message')->nullable();
            $table->decimal('setup_fees', 10, 2)->default(0);
            $table->decimal('registration_fees', 10, 2)->default(0);
            $table->decimal('admin_fees', 10, 2)->default(0);
            $table->decimal('bonus_malus', 10, 2)->default(0);
            $table->decimal('ww_fees', 10, 2)->default(0);
            $table->string('delivery_type');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
