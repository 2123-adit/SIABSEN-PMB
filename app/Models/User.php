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
        'user_id', // CHANGED: username -> user_id
        'name',
        'password',
        'jabatan_id',
        'status',
        'role',
        'jam_masuk',
        'jam_pulang',
        'password_changed',
        'password_changed_at',
        'foto_profil' // ADDED: foto profil field
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
    
    // QUERY OPTIMIZATION: Scopes for common queries
    public function scopeActiveUsers($query)
    {
        return $query->where('status', 'aktif');
    }
    
    public function scopeWithJabatan($query)
    {
        return $query->with('jabatan');
    }
    
    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }
}