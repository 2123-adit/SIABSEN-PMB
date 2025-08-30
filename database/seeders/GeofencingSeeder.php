<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeofencingSetting;

class GeofencingSeeder extends Seeder
{
    public function run(): void
    {
        // Default Geofencing Setting untuk Medan
        GeofencingSetting::create([
            'nama_lokasi' => 'Kantor ADMA Medan',
            'latitude' => 3.60079600, // Koordinat Medan, North Sumatra
            'longitude' => 98.71804350,
            'radius_meter' => 100, // 100 meter radius
            'deskripsi' => 'Area geofencing default untuk kantor ADMA di Medan',
            'is_active' => true
        ]);

        // Backup location (nonaktif)
        GeofencingSetting::create([
            'nama_lokasi' => 'Kantor Cabang ADMA',
            'latitude' => 3.5850,
            'longitude' => 98.6750,
            'radius_meter' => 150,
            'deskripsi' => 'Area geofencing untuk kantor cabang ADMA',
            'is_active' => false
        ]);
    }
}