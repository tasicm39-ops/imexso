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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('id_produit')->unique();
            $table->string('vin')->nullable();
            $table->string('make');
            $table->string('model');
            $table->string('trim_level');
            $table->string('fuel_type');
            $table->unsignedInteger('engine_displacement');
            $table->unsignedInteger('horsepower');
            $table->string('engine_code');
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedSmallInteger('manufacturing_year')->nullable();
            $table->string('gearbox');
            $table->string('gearbox_code');
            $table->string('color');
            $table->string('color_code')->nullable();
            $table->unsignedInteger('mileage');
            $table->unsignedSmallInteger('co2')->nullable();
            $table->unsignedSmallInteger('co2_wltp')->nullable();
            $table->unsignedSmallInteger('wltp_electric_range')->nullable();
            $table->string('euro_standard')->nullable();
            $table->decimal('professional_price', 10, 2);
            $table->string('vat_type');
            $table->json('status');
            $table->date('registration_date')->nullable();
            $table->date('retention_date')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->unsignedTinyInteger('doors')->nullable();
            $table->string('category')->nullable();
            $table->decimal('catalogue_base_price_excl_vat', 10, 2)->nullable();
            $table->decimal('catalogue_total_price_excl_vat', 10, 2)->nullable();
            $table->decimal('catalogue_price_incl_vat', 10, 2)->nullable();
            $table->decimal('used_car_fees', 10, 2)->default(0);
            $table->text('used_car_fees_detail')->nullable();
            $table->string('condition_type');
            $table->decimal('france_discount', 5, 2)->default(0);
            $table->string('catalogue_model_name')->nullable();
            $table->boolean('is_clearance')->default(false);
            $table->decimal('argus_price', 10, 2)->nullable();
            $table->decimal('previous_price', 10, 2)->nullable();
            $table->string('user_type');
            $table->boolean('is_new')->default(false);
            $table->text('catalogue_remark')->nullable();
            $table->date('creation_date');
            $table->decimal('private_price_incl_vat', 10, 2)->default(0);
            $table->string('publication_codes');
            $table->text('tags');
            $table->json('main_equipment_codes')->nullable();
            $table->boolean('carlab')->default(false);
            $table->string('carpass_url')->nullable();
            $table->decimal('ecological_penalty', 10, 2)->nullable();
            $table->string('vehicle_condition')->nullable();
            $table->json('auction_data')->nullable();
            $table->unsignedTinyInteger('stock_level')->nullable();
            $table->boolean('okcars')->default(false);
            $table->boolean('ecarlux')->default(false);
            $table->json('publish_platforms')->nullable();
            $table->json('excluded_countries')->nullable();
            $table->json('supplementary_equipment')->nullable();
            $table->json('standard_equipment')->nullable();
            $table->string('sync_status')->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('make');
            $table->index('fuel_type');
            $table->index('condition_type');
            $table->index('sync_status');
            $table->index('user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
