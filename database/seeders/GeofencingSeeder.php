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
            'nama_lokasi' => 'Kantor PMB Medan',
            'latitude' => 3.600348, // Koordinat Medan, North Sumatra
            'longitude' => 98.7178027,
            'radius_meter' => 100, // 100 meter radius
            'deskripsi' => 'Area geofencing default untuk kantor PMB di Medan',
            'is_active' => true
        ]);

        // Backup location (nonaktif)
        GeofencingSetting::create([
            'nama_lokasi' => 'Kantor Cabang PMB',
            'latitude' => 3.5850,
            'longitude' => 98.6750,
            'radius_meter' => 150,
            'deskripsi' => 'Area geofencing untuk kantor cabang PMB',
            'is_active' => false
        ]);
    }
}