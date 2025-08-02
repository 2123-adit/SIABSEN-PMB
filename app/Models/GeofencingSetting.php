<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeofencingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius_meter',
        'deskripsi',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_meter' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Ambil geofence aktif, dengan cache selama 1 jam.
     */
    public static function getActiveSetting()
    {
        return cache()->remember('active_geofence', 3600, function() {
            return self::where('is_active', true)->first();
        });
    }

    /**
     * Cek apakah koordinat user berada dalam radius.
     * Mengembalikan array dengan status, jarak, radius, dan nama lokasi.
     */
    public function isWithinRadius($userLat, $userLng): array
    {
        $distance = $this->calculateDistance($userLat, $userLng);
        $isWithin = $distance <= $this->radius_meter;

        return [
            'is_within' => $isWithin,
            'distance' => round($distance, 2),
            'radius' => $this->radius_meter,
            'location_name' => $this->nama_lokasi
        ];
    }

    /**
     * Hitung jarak antara titik user dan titik geofence menggunakan Haversine.
     * Keluaran dalam meter.
     */
    private function calculateDistance($lat1, $lng1): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($this->latitude);
        $lng2Rad = deg2rad($this->longitude);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }
}
