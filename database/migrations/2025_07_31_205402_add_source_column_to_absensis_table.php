<?php
// database/migrations/2025_01_XX_add_source_column_to_absensis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Tambah kolom source dengan default 'mobile'
            $table->enum('source', ['mobile', 'manual', 'bulk'])->default('mobile')->after('keterangan');
            
            // Index untuk performa query
            $table->index(['source', 'tanggal']);
        });
        
        // UPDATE existing data berdasarkan karakteristik
        DB::statement("
            UPDATE absensis 
            SET source = CASE 
                WHEN foto_masuk IS NOT NULL AND foto_pulang IS NOT NULL THEN 'mobile'
                WHEN (jam_masuk IS NOT NULL OR jam_pulang IS NOT NULL) AND (foto_masuk IS NULL OR foto_pulang IS NULL) THEN 'manual'
                ELSE 'bulk'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropIndex(['source', 'tanggal']);
            $table->dropColumn('source');
        });
    }
};