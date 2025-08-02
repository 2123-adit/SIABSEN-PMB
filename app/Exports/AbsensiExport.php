<?php
// app/Exports/AbsensiExport.php - FIXED V2

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $absensis;
    protected $filters;

    public function __construct($absensis, $filters)
    {
        $this->absensis = $absensis;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->absensis;
    }

    public function headings(): array
    {
        return [
            'No',
            'Username',
            'Nama',
            'Jabatan',
            'Tanggal',
            'Hari',
            'Jam Masuk',
            'Jam Pulang',
            'Status Kehadiran',
            'Status Masuk',
            'Menit Terlambat',
            'Total Jam Kerja',
            'Lokasi Masuk',
            'Lokasi Pulang',
            'Geofencing Masuk',
            'Geofencing Pulang',
            'Jarak Masuk (m)',
            'Jarak Pulang (m)',
            'Source',
            'Keterangan'
        ];
    }

    public function map($absensi): array
    {
        static $no = 1;

        // FIXED: Properly check if this is a virtual/generated record
        $isVirtualRecord = !$absensi->exists || 
                          ($absensi->source ?? '') === 'system_generated' ||
                          ($absensi->status_kehadiran === 'alfa' && 
                           is_null($absensi->jam_masuk) && 
                           is_null($absensi->jam_pulang) && 
                           is_null($absensi->foto_masuk));

        // FIXED: Proper time formatting
        $jamMasuk = '-';
        $jamPulang = '-';
        
        if ($absensi->jam_masuk) {
            if (is_string($absensi->jam_masuk)) {
                $jamMasuk = date('H:i', strtotime($absensi->jam_masuk));
            } else {
                $jamMasuk = $absensi->jam_masuk->format('H:i');
            }
        }
        
        if ($absensi->jam_pulang) {
            if (is_string($absensi->jam_pulang)) {
                $jamPulang = date('H:i', strtotime($absensi->jam_pulang));
            } else {
                $jamPulang = $absensi->jam_pulang->format('H:i');
            }
        }

        // FIXED: Calculate total working hours properly
        $totalJamKerja = '0 jam';
        if (!$isVirtualRecord && $absensi->jam_masuk && $absensi->jam_pulang) {
            try {
                $masuk = is_string($absensi->jam_masuk) ? 
                        \Carbon\Carbon::createFromFormat('H:i:s', $absensi->jam_masuk) : 
                        $absensi->jam_masuk;
                        
                $pulang = is_string($absensi->jam_pulang) ? 
                         \Carbon\Carbon::createFromFormat('H:i:s', $absensi->jam_pulang) : 
                         $absensi->jam_pulang;
                
                $diffInHours = $pulang->diffInHours($masuk, true);
                $totalJamKerja = number_format($diffInHours, 1) . ' jam';
            } catch (\Exception $e) {
                $totalJamKerja = '0 jam';
            }
        }

        return [
            $no++,
            $absensi->user->username ?? 'N/A',
            $absensi->user->name ?? 'N/A',
            $absensi->user->jabatan->nama_jabatan ?? 'N/A',
            $absensi->tanggal->format('d/m/Y'),
            $absensi->tanggal->locale('id')->dayName,
            $jamMasuk,
            $jamPulang,
            ucfirst($absensi->status_kehadiran),
            $absensi->status_masuk ? ucfirst(str_replace('_', ' ', $absensi->status_masuk)) : '-',
            $absensi->menit_terlambat ?? 0,
            $totalJamKerja,
            // Location info
            $isVirtualRecord ? '-' : ($this->getGoogleMapsLink($absensi->latitude_masuk, $absensi->longitude_masuk) ?? '-'),
            $isVirtualRecord ? '-' : ($this->getGoogleMapsLink($absensi->latitude_pulang, $absensi->longitude_pulang) ?? '-'),
            // Geofencing status
            $isVirtualRecord ? '-' : ($absensi->is_within_geofence_masuk ? 'Dalam Area' : 'Luar Area'),
            $isVirtualRecord ? '-' : ($absensi->is_within_geofence_pulang ? 'Dalam Area' : 'Luar Area'),
            // Distance
            $isVirtualRecord ? '-' : ($absensi->distance_from_office_masuk ?? '-'),
            $isVirtualRecord ? '-' : ($absensi->distance_from_office_pulang ?? '-'),
            // Source
            $this->getSourceText($absensi->source ?? ($isVirtualRecord ? 'system_generated' : 'unknown')),
            $absensi->keterangan ?? ($isVirtualRecord ? 'Tidak melakukan absensi' : '-')
        ];
    }

    private function getGoogleMapsLink($lat, $lng): ?string
    {
        if ($lat && $lng) {
            return "https://www.google.com/maps?q={$lat},{$lng}";
        }
        return null;
    }

    private function getSourceText($source): string
    {
        $sources = [
            'mobile' => '📱 Mobile App',
            'manual' => '✏️ Manual Input', 
            'bulk' => '📝 Bulk Input',
            'system_generated' => '🤖 System Generated',
            'unknown' => '❓ Unknown'
        ];

        return $sources[$source] ?? '❓ Unknown';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ]
            ],
            // Data rows
            'A2:T1000' => [
                'font' => [
                    'size' => 10
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // Username
            'C' => 25,  // Nama
            'D' => 20,  // Jabatan
            'E' => 12,  // Tanggal
            'F' => 12,  // Hari
            'G' => 12,  // Jam Masuk
            'H' => 12,  // Jam Pulang
            'I' => 15,  // Status Kehadiran
            'J' => 15,  // Status Masuk
            'K' => 12,  // Menit Terlambat
            'L' => 15,  // Total Jam Kerja
            'M' => 30,  // Lokasi Masuk
            'N' => 30,  // Lokasi Pulang
            'O' => 15,  // Geofencing Masuk
            'P' => 15,  // Geofencing Pulang
            'Q' => 12,  // Jarak Masuk
            'R' => 12,  // Jarak Pulang
            'S' => 15,  // Source
            'T' => 25,  // Keterangan
        ];
    }
}