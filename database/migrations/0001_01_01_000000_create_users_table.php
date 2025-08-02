
<?php
// database/migrations/2024_01_01_000002_create_users_table.php - UPDATE

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); // CHANGED: NIK -> Username
            $table->string('name');
            // REMOVED: email, phone, alamat, gender, tanggal_lahir, foto_profil
            $table->unsignedBigInteger('jabatan_id');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->time('jam_masuk')->default('08:00:00');
            $table->time('jam_pulang')->default('17:00:00');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('cascade');
            $table->index(['username', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};