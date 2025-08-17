<?php

namespace App\Http\Controllers\Api;
use App\Models\Absensi;
use App\Models\Holiday;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends BaseApiController
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $user->load('jabatan'); // EAGER LOAD jabatan relation
        $today = Carbon::today();

        // Cek hari libur
        $isHoliday = Holiday::isHoliday($today);
        
        // Cek jadwal kerja berdasarkan jabatan
        $isWorkingDay = $user->jabatan->isWorkingDay($today);

        // Absensi hari ini
        $absensiHariIni = $user->absensiHariIni();

        // Statistik bulan ini
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $statistik = [
            'total_hadir' => $user->absensis()
                ->where('status_kehadiran', 'hadir')
                ->whereMonth('tanggal', $thisMonth)
                ->whereYear('tanggal', $thisYear)
                ->count(),
            'total_terlambat' => $user->absensis()
                ->where('status_masuk', 'terlambat')
                ->whereMonth('tanggal', $thisMonth)
                ->whereYear('tanggal', $thisYear)
                ->count(),
            'total_izin' => $user->absensis()
                ->where('status_kehadiran', 'izin')
                ->whereMonth('tanggal', $thisMonth)
                ->whereYear('tanggal', $thisYear)
                ->count(),
            'persentase_kehadiran' => $user->persentase_kehadiran
        ];

        return $this->successResponse([
            'user' => [
                'name' => $user->name,
                'jabatan' => $user->jabatan->nama_jabatan,
                'jam_masuk' => $user->jam_masuk,
                'jam_pulang' => $user->jam_pulang,
                'jadwal_kerja' => $user->jabatan->jadwal_kerja
            ],
            'today' => [
                'tanggal' => $today->format('Y-m-d'),
                'is_holiday' => $isHoliday,
                'is_working_day' => $isWorkingDay,
                'can_attend' => $isWorkingDay && !$isHoliday,
                'absensi' => $absensiHariIni ? [
                    'jam_masuk' => $absensiHariIni->jam_masuk,
                    'jam_pulang' => $absensiHariIni->jam_pulang,
                    'status_masuk' => $absensiHariIni->status_masuk,
                    'status_pulang' => $absensiHariIni->status_pulang,
                    'sudah_absen_masuk' => $absensiHariIni->sudahAbsenMasuk(),
                    'sudah_absen_pulang' => $absensiHariIni->sudahAbsenPulang()
                ] : null
            ],
            'statistik' => $statistik
        ], 'Dashboard data berhasil diambil');
    }

    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        try {
            $result = $this->absensiService->absenMasuk(
                $request->user(),
                $request->latitude,
                $request->longitude,
                $request->file('foto')
            );

            return $this->successResponse($result, 'Absen masuk berhasil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ABSEN_MASUK_ERROR', 400);
        }
    }

    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        try {
            $result = $this->absensiService->absenPulang(
                $request->user(),
                $request->latitude,
                $request->longitude,
                $request->file('foto')
            );

            return $this->successResponse($result, 'Absen pulang berhasil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ABSEN_PULANG_ERROR', 400);
        }
    }

    public function kalender(Request $request)
    {
        $user = $request->user();
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        $absensis = $user->absensis()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get()
            ->map(function($absensi) {
                return [
                    'tanggal' => $absensi->tanggal->format('Y-m-d'),
                    'jam_masuk' => $absensi->jam_masuk,
                    'jam_pulang' => $absensi->jam_pulang,
                    'status_kehadiran' => $absensi->status_kehadiran,
                    'status_masuk' => $absensi->status_masuk,
                    'foto_masuk' => $absensi->foto_masuk ? asset('storage/' . $absensi->foto_masuk) : null,
                    'foto_pulang' => $absensi->foto_pulang ? asset('storage/' . $absensi->foto_pulang) : null,
                    'google_maps_masuk' => $absensi->google_maps_link_masuk,
                    'google_maps_pulang' => $absensi->google_maps_link_pulang,
                    'total_jam_kerja' => $absensi->total_jam_kerja
                ];
            });

        return $this->successResponse($absensis, 'Data kalender absensi berhasil diambil');
    }

    public function riwayat(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $limit;

        // Get total count
        $total = $user->absensis()->count();

        $absensis = $user->absensis()
            ->orderBy('tanggal', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function($absensi) {
                return [
                    'id' => $absensi->id,
                    'tanggal' => $absensi->tanggal->format('Y-m-d'),
                    'hari' => $absensi->tanggal->format('l'),
                    'jam_masuk' => $absensi->jam_masuk,
                    'jam_pulang' => $absensi->jam_pulang,
                    'status_kehadiran' => $absensi->status_kehadiran,
                    'status_masuk' => $absensi->status_masuk,
                    'menit_terlambat' => $absensi->menit_terlambat,
                    'foto_masuk' => $absensi->foto_masuk ? asset('storage/' . $absensi->foto_masuk) : null,
                    'foto_pulang' => $absensi->foto_pulang ? asset('storage/' . $absensi->foto_pulang) : null
                ];
            });

        $meta = [
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit),
            'from' => $offset + 1,
            'to' => min($offset + $limit, $total)
        ];

        return $this->successWithMeta($absensis, $meta, 'Riwayat absensi berhasil diambil');
    }

    /**
     * Check geofence status (untuk preview sebelum submit absensi)
     * Endpoint: POST /api/absensi/check-geofence
     */
    public function checkGeofence(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $latitude = $request->latitude;
            $longitude = $request->longitude;

            \Log::info('=== GEOFENCE CHECK API REQUEST ===', [
                'user_id' => auth()->id(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'time' => now()->toDateTimeString(),
            ]);

            // Panggil service untuk validasi geofencing
            $geofenceResult = $this->absensiService->validateGeofencing($latitude, $longitude);

            \Log::info('=== GEOFENCE CHECK API RESULT ===', [
                'result' => $geofenceResult,
            ]);

            return response()->json([
                'success' => true,
                'message' => $geofenceResult['is_within'] 
                    ? 'Anda berada dalam area kantor' 
                    : 'Anda berada di luar area kantor',
                'data' => [
                    'is_within' => $geofenceResult['is_within'],
                    'distance' => $geofenceResult['distance'],
                    'radius' => $geofenceResult['geofence_radius'],
                    'location_name' => $geofenceResult['location_name'],
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in checkGeofence: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa lokasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Method untuk route /api/geofencing/check
     */
    public function checkGeofencing(Request $request)
    {
        // Delegate ke method checkGeofence yang sudah ada       
        return $this->checkGeofence($request);
    }

    /**
     * Get active geofencing settings
     * Endpoint: GET /api/geofencing/settings
     */
    public function getGeofencingSettings()
    {
        try {
            $geofencingSetting = \App\Models\GeofencingSetting::getActiveSetting();

            if (!$geofencingSetting) {
                return $this->errorResponse('Pengaturan geofencing tidak ditemukan', 'GEOFENCING_NOT_FOUND', 404);       
            }

            return $this->successResponse($geofencingSetting, 'Pengaturan geofencing berhasil diambil');
        } catch (\Exception $e) {
            \Log::error('Error getting geofencing settings: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil pengaturan geofencing', 'GEOFENCING_ERROR', 500);
        }
    }
}