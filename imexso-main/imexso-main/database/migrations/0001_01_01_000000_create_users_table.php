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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->string('legacy_user_id', 15)->nullable()->unique();
            $table->string('legacy_client_id', 15)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('language', 5)->nullable();
            $table->string('user_type', 15)->nullable();
            $table->string('company_name', 200)->nullable();
            $table->string('legal_form', 20)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('vat_number', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_validated')->default(false);
            $table->boolean('is_seller')->default(false);
            $table->string('activity', 150)->nullable();
            $table->string('website', 250)->nullable();
            $table->date('last_contact_date')->nullable();
            $table->date('last_order_date')->nullable();
            $table->string('new_car_seller', 100)->nullable();
            $table->string('used_car_seller', 100)->nullable();
            $table->string('logo', 255)->nullable();
            $table->text('slogan')->nullable();
            $table->unsignedInteger('car_volume')->default(0);
            $table->decimal('revenue', 10, 2)->default(0);
            $table->text('activity_follow_up')->nullable();
            $table->text('vehicles_searched')->nullable();
            $table->text('comments')->nullable();
            $table->string('old_email', 50)->nullable();

            $table->timestamps();

            $table->index('country');
            $table->index('user_type');
            $table->index('is_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
