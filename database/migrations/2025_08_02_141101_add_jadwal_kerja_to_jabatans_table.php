<?php
// database/migrations/2025_08_02_000001_add_jadwal_kerja_to_jabatans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Jadwal hari kerja (JSON untuk fleksibilitas) - nullable untuk menghindari error default value
            $table->json('jadwal_kerja')->nullable()->after('toleransi_terlambat');
            
            // Keterangan jadwal
            $table->string('keterangan_jadwal')->nullable()->after('jadwal_kerja');
        });

        // Set default value setelah kolom dibuat
        DB::table('jabatans')->whereNull('jadwal_kerja')->update([
            'jadwal_kerja' => json_encode([
                'senin' => true,
                'selasa' => true,
                'rabu' => true,
                'kamis' => true,
                'jumat' => true,
                'sabtu' => false,
                'minggu' => false
            ])
        ]);
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropColumn(['jadwal_kerja', 'keterangan_jadwal']);
        });
    }
};