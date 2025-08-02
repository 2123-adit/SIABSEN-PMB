<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'foto_masuk',
        'foto_pulang',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'status_masuk',
        'status_pulang',
        'status_kehadiran',
        'keterangan',
        'menit_terlambat',
        // Geofencing fields
        'is_within_geofence_masuk',
        'is_within_geofence_pulang',
        'distance_from_office_masuk',
        'distance_from_office_pulang',
        // NEW: Source field
        'source'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i:s',
        'jam_pulang' => 'datetime:H:i:s',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_pulang' => 'decimal:8',
        'longitude_pulang' => 'decimal:8',
        'is_within_geofence_masuk' => 'boolean',
        'is_within_geofence_pulang' => 'boolean',
        'distance_from_office_masuk' => 'decimal:2',
        'distance_from_office_pulang' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'hadir' => 'success',
            'izin' => 'warning',
            'sakit' => 'info',
            'alfa' => 'danger'
        ];

        return $badges[$this->status_kehadiran] ?? 'secondary';
    }

    // NEW: Source Badge
    public function getSourceBadgeAttribute()
    {
        $badges = [
            'mobile' => ['class' => 'bg-primary', 'icon' => '📱', 'text' => 'Mobile'],
            'manual' => ['class' => 'bg-info', 'icon' => '✏️', 'text' => 'Manual'],
            'bulk' => ['class' => 'bg-success', 'icon' => '📝', 'text' => 'Bulk']
        ];

        return $badges[$this->source] ?? ['class' => 'bg-secondary', 'icon' => '❓', 'text' => 'Unknown'];
    }

    // NEW: Auto-detect source berdasarkan data
    public function detectSource()
    {
        if ($this->foto_masuk && $this->foto_pulang) {
            return 'mobile';
        } elseif ($this->jam_masuk || $this->jam_pulang) {
            return 'manual';
        } else {
            return 'bulk';
        }
    }

    // Scope untuk reporting: filter berdasarkan rentang tanggal
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }

    // Scope untuk reporting: filter berdasarkan status kehadiran
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_kehadiran', $status);
    }

    public function getGoogleMapsLinkMasukAttribute()
    {
        if ($this->latitude_masuk && $this->longitude_masuk) {
            return "https://www.google.com/maps?q={$this->latitude_masuk},{$this->longitude_masuk}";
        }
        return null;
    }

    public function getGoogleMapsLinkPulangAttribute()
    {
        if ($this->latitude_pulang && $this->longitude_pulang) {
            return "https://www.google.com/maps?q={$this->latitude_pulang},{$this->longitude_pulang}";
        }
        return null;
    }

    public function getTotalJamKerjaAttribute()
    {
        if ($this->jam_masuk && $this->jam_pulang) {
            $masuk = Carbon::parse($this->jam_masuk);
            $pulang = Carbon::parse($this->jam_pulang);
            
            return $masuk->diffInHours($pulang, true);
        }
        return 0;
    }

    public function sudahAbsenMasuk(): bool
    {
        return !is_null($this->jam_masuk);
    }

    public function sudahAbsenPulang(): bool
    {
        return !is_null($this->jam_pulang);
    }

    public function hitungStatusMasuk($jamKerja)
    {
        if (!$this->jam_masuk) return null;

        $jamMasukUser = Carbon::parse($this->jam_masuk)->setTimezone('Asia/Jakarta');
        $jamKerjaTentukan = Carbon::parse($jamKerja)->setTimezone('Asia/Jakarta');
        $toleransi = $this->user->jabatan->toleransi_terlambat ?? 15;

        if ($jamMasukUser->isAfter($jamKerjaTentukan->addMinutes($toleransi))) {
            $this->menit_terlambat = $jamMasukUser->diffInMinutes($jamKerjaTentukan);
            return 'terlambat';
        }

        return 'tepat_waktu';
    }

    public function getGeofenceStatusMasukAttribute()
    {
        if ($this->is_within_geofence_masuk) {
            return ['status' => 'dalam_area', 'color' => 'success', 'text' => 'Dalam Area'];
        }
        return ['status' => 'luar_area', 'color' => 'danger', 'text' => 'Luar Area'];
    }

    public function getGeofenceStatusPulangAttribute()
    {
        if ($this->is_within_geofence_pulang) {
            return ['status' => 'dalam_area', 'color' => 'success', 'text' => 'Dalam Area'];
        }
        return ['status' => 'luar_area', 'color' => 'danger', 'text' => 'Luar Area'];
    }
}
