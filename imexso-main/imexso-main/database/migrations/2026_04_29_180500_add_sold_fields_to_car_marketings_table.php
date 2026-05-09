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
        Schema::table('car_marketings', function (Blueprint $table) {
            $table->boolean('sold_enabled')->default(false)->after('badge_text');
            $table->unsignedSmallInteger('sold_visible_days')->default(5)->after('sold_enabled');
            $table->timestamp('sold_marked_at')->nullable()->after('sold_visible_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_marketings', function (Blueprint $table) {
            $table->dropColumn(['sold_enabled', 'sold_visible_days', 'sold_marked_at']);
        });
    }
};
