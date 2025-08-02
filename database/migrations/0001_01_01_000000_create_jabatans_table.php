<?php
// database/migrations/2024_01_01_000001_create_jabatans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->text('deskripsi')->nullable();
            $table->integer('toleransi_terlambat')->default(15); // dalam menit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatans');
    }
};