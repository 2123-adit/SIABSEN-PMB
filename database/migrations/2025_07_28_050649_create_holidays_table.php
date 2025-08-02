<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_libur');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['nasional', 'cuti_bersama', 'khusus'])->default('nasional');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('tanggal');
            $table->index(['tanggal', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
