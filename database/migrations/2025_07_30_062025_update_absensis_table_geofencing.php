<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->boolean('is_within_geofence_masuk')->default(false)->after('longitude_masuk');
            $table->boolean('is_within_geofence_pulang')->default(false)->after('longitude_pulang');
            $table->decimal('distance_from_office_masuk', 8, 2)->nullable()->after('is_within_geofence_masuk');
            $table->decimal('distance_from_office_pulang', 8, 2)->nullable()->after('is_within_geofence_pulang');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn([
                'is_within_geofence_masuk',
                'is_within_geofence_pulang', 
                'distance_from_office_masuk',
                'distance_from_office_pulang'
            ]);
        });
    }
};