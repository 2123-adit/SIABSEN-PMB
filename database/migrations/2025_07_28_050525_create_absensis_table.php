<?php

// database/migrations/2024_01_01_000003_create_absensis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_pulang', 10, 8)->nullable();
            $table->decimal('longitude_pulang', 11, 8)->nullable();
            $table->enum('status_masuk', ['tepat_waktu', 'terlambat'])->nullable();
            $table->enum('status_pulang', ['tepat_waktu', 'lebih_awal'])->nullable();
            $table->enum('status_kehadiran', ['hadir', 'izin', 'sakit', 'alfa'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->integer('menit_terlambat')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'tanggal']);
            $table->index(['tanggal', 'status_kehadiran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};