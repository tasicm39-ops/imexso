<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_histories', function (Blueprint $table) {
            $table->id();
            $table->string('id_produit', 10);
            $table->string('vin', 100);
            $table->string('condition_type', 20);
            $table->string('make', 100);
            $table->string('model', 100);
            $table->string('trim_level', 100)->nullable();
            $table->string('radio_code', 50)->nullable();
            $table->string('color', 200)->nullable();
            $table->string('location', 50)->nullable();
            $table->string('price', 200)->nullable();
            $table->string('tax_type', 10)->nullable();
            $table->string('client_id', 15);
            $table->string('status', 20);
            $table->date('order_date')->nullable();
            $table->text('documents')->nullable();
            $table->text('invoices')->nullable();
            $table->string('to_unblock', 5)->nullable();
            $table->integer('assignment_count')->nullable();
            $table->text('assignment_1')->nullable();
            $table->text('assignment_2')->nullable();
            $table->integer('days_available')->default(0);
            $table->string('lot_number', 255)->nullable();
            $table->timestamps();

            $table->index('id_produit');
            $table->index('client_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_histories');
    }
};
