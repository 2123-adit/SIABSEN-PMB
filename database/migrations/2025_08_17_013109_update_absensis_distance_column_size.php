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
        Schema::table('absensis', function (Blueprint $table) {
            // Change distance columns to DECIMAL(12,2) to support large distances
            $table->decimal('distance_from_office_masuk', 12, 2)->nullable()->change();
            $table->decimal('distance_from_office_pulang', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Revert to smaller precision (assuming original was 8,2)
            $table->decimal('distance_from_office_masuk', 8, 2)->nullable()->change();
            $table->decimal('distance_from_office_pulang', 8, 2)->nullable()->change();
        });
    }
};
