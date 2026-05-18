<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique(['id_produit']);
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->string('stock_status', 32)->default('AVAILABLE')->after('sync_status');
            $table->index('id_produit');
            $table->index('stock_status');
        });

        DB::table('cars')->where('sync_status', 'sold')->update(['stock_status' => 'SOLD']);
        DB::table('cars')->where('sync_status', 'active')->update(['stock_status' => 'AVAILABLE']);

        Schema::create('car_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->string('status', 32);
            $table->json('buyer_info')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('car_id')->references('id')->on('cars')->cascadeOnUpdate();
            $table->unique(['car_id', 'status']);
            $table->index('car_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_history');

        Schema::table('cars', function (Blueprint $table) {
            $table->dropIndex(['id_produit']);
            $table->dropIndex(['stock_status']);
            $table->dropColumn('stock_status');
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->unique('id_produit');
        });
    }
};
