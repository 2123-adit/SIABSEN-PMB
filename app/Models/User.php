<?php
// app/Models/User.php - UPDATED

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username', // CHANGED: nik -> username
        'name',
        'password',
        'jabatan_id',
        'status',
        'role',
        'jam_masuk',
        'jam_pulang'
        // REMOVED: email, phone, alamat, gender, tanggal_lahir, foto_profil
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i'
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function absensiHariIni()
    {
        return $this->absensis()->whereDate('tanggal', today('Asia/Jakarta'))->first();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getPersentaseKehadiranAttribute()
    {
        $totalHari = $this->absensis()->count();
        $hadir = $this->absensis()->where('status_kehadiran', 'hadir')->count();
        
        return $totalHari > 0 ? round(($hadir / $totalHari) * 100, 2) : 0;
    }

    public function getTotalTerlambatAttribute()
    {
        return $this->absensis()->where('status_masuk', 'terlambat')->count();
    }
}