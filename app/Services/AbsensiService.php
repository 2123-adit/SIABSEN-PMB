<?php
// app/Services/AbsensiService.php - UPDATED with Geofencing

namespace App\Services;

use App\Models\Absensi;
use App\Models\Holiday;
use App\Models\User;
use App\Models\GeofencingSetting;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AbsensiService
{
    public function absenMasuk(User $user, $latitude, $longitude, UploadedFile $foto)
    {
        // Set timezone ke Asia/Jakarta (sama dengan Medan)
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        // Validasi hari libur
        if (Holiday::isHoliday($today)) {
            throw new \Exception('Tidak dapat absen pada hari libur');
        }

        // Cek apakah sudah absen masuk hari ini
        $absensiHariIni = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($absensiHariIni && $absensiHariIni->sudahAbsenMasuk()) {
            throw new \Exception('Anda sudah absen masuk hari ini');
        }

        // Validasi Geofencing
        $geofenceResult = $this->validateGeofencing($latitude, $longitude);
        if (!$geofenceResult['is_within']) {
            throw new \Exception(
                "Anda berada di luar area kantor. Jarak: {$geofenceResult['distance']}m dari {$geofenceResult['location_name']} (Max: {$geofenceResult['radius']}m)"
            );
        }

        // Upload foto
        $fotoPath = $foto->store('absensi/masuk/' . $today->format('Y/m'), 'public');

        // Hitung status keterlambatan dengan timezone Asia/Jakarta
        $jamMasuk = $now;
        $jamKerjaMulai = Carbon::parse($user->jam_masuk)->setTimezone('Asia/Jakarta');
        $jamKerjaMulai->setDate($today->year, $today->month, $today->day);
        $toleransi = $user->jabatan->toleransi_terlambat ?? 15;

        $statusMasuk = 'tepat_waktu';
        $menitTerlambat = 0;

        if ($jamMasuk->isAfter($jamKerjaMulai->copy()->addMinutes($toleransi))) {
            $statusMasuk = 'terlambat';
            $menitTerlambat = $jamMasuk->diffInMinutes($jamKerjaMulai);
        }

        // Simpan atau update absensi
        if ($absensiHariIni) {
            $absensiHariIni->update([
                'jam_masuk' => $jamMasuk->format('H:i:s'),
                'foto_masuk' => $fotoPath,
                'latitude_masuk' => $latitude,
                'longitude_masuk' => $longitude,
                'status_masuk' => $statusMasuk,
                'menit_terlambat' => $menitTerlambat,
                'status_kehadiran' => 'hadir',
                'is_within_geofence_masuk' => $geofenceResult['is_within'],
                'distance_from_office_masuk' => $geofenceResult['distance']
            ]);
        } else {
            $absensiHariIni = Absensi::create([
                'user_id' => $user->id,
                'tanggal' => $today,
                'jam_masuk' => $jamMasuk->format('H:i:s'),
                'foto_masuk' => $fotoPath,
                'latitude_masuk' => $latitude,
                'longitude_masuk' => $longitude,
                'status_masuk' => $statusMasuk,
                'menit_terlambat' => $menitTerlambat,
                'status_kehadiran' => 'hadir',
                'is_within_geofence_masuk' => $geofenceResult['is_within'],
                'distance_from_office_masuk' => $geofenceResult['distance']
            ]);
        }

        return [
            'jam_masuk' => $jamMasuk->format('H:i:s'),
            'status_masuk' => $statusMasuk,
            'menit_terlambat' => $menitTerlambat,
            'foto_url' => asset('storage/' . $fotoPath),
            'google_maps' => "https://www.google.com/maps?q={$latitude},{$longitude}",
            'geofence_status' => $geofenceResult['is_within'] ? 'Dalam Area' : 'Luar Area',
            'distance_from_office' => $geofenceResult['distance'],
            'server_time' => $now->format('Y-m-d H:i:s T'),
            'timezone' => 'Asia/Jakarta'
        ];
    }

    public function absenPulang(User $user, $latitude, $longitude, UploadedFile $foto)
    {
        // Set timezone ke Asia/Jakarta
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        // Validasi hari libur
        if (Holiday::isHoliday($today)) {
            throw new \Exception('Tidak dapat absen pada hari libur');
        }

        // Cek absensi hari ini
        $absensiHariIni = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$absensiHariIni || !$absensiHariIni->sudahAbsenMasuk()) {
            throw new \Exception('Anda belum absen masuk hari ini');
        }

        if ($absensiHariIni->sudahAbsenPulang()) {
            throw new \Exception('Anda sudah absen pulang hari ini');
        }

        // Validasi Geofencing
        $geofenceResult = $this->validateGeofencing($latitude, $longitude);
        if (!$geofenceResult['is_within']) {
            throw new \Exception(
                "Anda berada di luar area kantor. Jarak: {$geofenceResult['distance']}m dari {$geofenceResult['location_name']} (Max: {$geofenceResult['radius']}m)"
            );
        }

        // Upload foto
        $fotoPath = $foto->store('absensi/pulang/' . $today->format('Y/m'), 'public');

        // Hitung status pulang dengan timezone Asia/Jakarta
        $jamPulang = $now;
        $jamKerjaBerakhir = Carbon::parse($user->jam_pulang)->setTimezone('Asia/Jakarta');
        $jamKerjaBerakhir->setDate($today->year, $today->month, $today->day);

        $statusPulang = 'tepat_waktu';
        if ($jamPulang->isBefore($jamKerjaBerakhir)) {
            $statusPulang = 'lebih_awal';
        }

        // Update absensi
        $absensiHariIni->update([
            'jam_pulang' => $jamPulang->format('H:i:s'),
            'foto_pulang' => $fotoPath,
            'latitude_pulang' => $latitude,
            'longitude_pulang' => $longitude,
            'status_pulang' => $statusPulang,
            'is_within_geofence_pulang' => $geofenceResult['is_within'],
            'distance_from_office_pulang' => $geofenceResult['distance']
        ]);

        return [
            'jam_pulang' => $jamPulang->format('H:i:s'),
            'status_pulang' => $statusPulang,
            'foto_url' => asset('storage/' . $fotoPath),
            'google_maps' => "https://www.google.com/maps?q={$latitude},{$longitude}",
            'total_jam_kerja' => $absensiHariIni->total_jam_kerja,
            'geofence_status' => $geofenceResult['is_within'] ? 'Dalam Area' : 'Luar Area',
            'distance_from_office' => $geofenceResult['distance'],
            'server_time' => $now->format('Y-m-d H:i:s T'),
            'timezone' => 'Asia/Jakarta'
        ];
    }

    private function validateGeofencing($userLat, $userLng): array
    {
        $geofenceSetting = GeofencingSetting::getActiveSetting();
        
        if (!$geofenceSetting) {
            // Jika tidak ada setting geofencing, izinkan absen dari mana saja
            return [
                'is_within' => true,
                'distance' => 0,
                'radius' => 0,
                'location_name' => 'No Geofencing'
            ];
        }

        return $geofenceSetting->isWithinRadius($userLat, $userLng);
    }

    public function getStatistikUser(User $user, $bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? Carbon::now('Asia/Jakarta')->month;
        $tahun = $tahun ?? Carbon::now('Asia/Jakarta')->year;

        $query = $user->absensis()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        return [
            'total_hadir' => $query->clone()->where('status_kehadiran', 'hadir')->count(),
            'total_izin' => $query->clone()->where('status_kehadiran', 'izin')->count(),
            'total_sakit' => $query->clone()->where('status_kehadiran', 'sakit')->count(),
            'total_alfa' => $query->clone()->where('status_kehadiran', 'alfa')->count(),
            'total_terlambat' => $query->clone()->where('status_masuk', 'terlambat')->count(),
            'rata_rata_jam_kerja' => $query->clone()->whereNotNull('jam_pulang')->avg('total_jam_kerja') ?? 0,
        ];
    }

    public function getRiwayatAbsensi(User $user, $filter = 'mingguan', $page = 1, $limit = 10)
    {
        $now = Carbon::now('Asia/Jakarta');
        $query = $user->absensis();

        switch ($filter) {
            case 'mingguan':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'bulanan':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case '7_hari':
                $startDate = $now->copy()->subDays(7);
                $endDate = $now->copy();
                break;
            case '30_hari':
                $startDate = $now->copy()->subDays(30);
                $endDate = $now->copy();
                break;
            default:
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
        }

        $absensis = $query->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $total = $query->whereBetween('tanggal', [$startDate, $endDate])->count();

        return [
            'data' => $absensis->map(function($absensi) {
                return [
                    'id' => $absensi->id,
                    'tanggal' => $absensi->tanggal->format('Y-m-d'),
                    'hari' => $absensi->tanggal->format('l'),
                    'jam_masuk' => $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i') : null,
                    'jam_pulang' => $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i') : null,
                    'status_kehadiran' => $absensi->status_kehadiran,
                    'status_masuk' => $absensi->status_masuk,
                    'menit_terlambat' => $absensi->menit_terlambat,
                    'foto_masuk' => $absensi->foto_masuk ? asset('storage/' . $absensi->foto_masuk) : null,
                    'foto_pulang' => $absensi->foto_pulang ? asset('storage/' . $absensi->foto_pulang) : null,
                    'geofence_masuk' => $absensi->geofence_status_masuk,
                    'geofence_pulang' => $absensi->geofence_status_pulang,
                    'distance_masuk' => $absensi->distance_from_office_masuk,
                    'distance_pulang' => $absensi->distance_from_office_pulang,
                    'total_jam_kerja' => $absensi->total_jam_kerja
                ];
            }),
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_records' => $total,
                'per_page' => $limit,
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ],
            'filter_info' => [
                'filter' => $filter,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'timezone' => 'Asia/Jakarta'
            ]
        ];
    }

    public function checkServerTime()
    {
        $now = Carbon::now('Asia/Jakarta');
        
        return [
            'server_time' => $now->format('Y-m-d H:i:s'),
            'server_timestamp' => $now->timestamp,
            'timezone' => 'Asia/Jakarta',
            'timezone_offset' => $now->format('P'),
            'day_name' => $now->format('l'),
            'formatted_time' => $now->format('d F Y, H:i:s'),
            'is_working_hours' => $this->isWorkingHours($now),
            'is_holiday' => Holiday::isHoliday($now->toDateString())
        ];
    }

    private function isWorkingHours(Carbon $time): bool
    {
        $hour = $time->hour;
        $dayOfWeek = $time->dayOfWeek;
        
        // Senin-Jumat (1-5), jam 06:00-20:00
        return $dayOfWeek >= 1 && $dayOfWeek <= 5 && $hour >= 6 && $hour <= 20;
    }
}