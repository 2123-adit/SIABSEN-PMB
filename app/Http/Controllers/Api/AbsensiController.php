<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Holiday;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // Cek hari libur
        $isHoliday = Holiday::isHoliday($today);

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

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'jabatan' => $user->jabatan->nama_jabatan,
                    'jam_masuk' => $user->jam_masuk,
                    'jam_pulang' => $user->jam_pulang
                ],
                'today' => [
                    'tanggal' => $today->format('Y-m-d'),
                    'is_holiday' => $isHoliday,
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
            ]
        ]);
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

            return response()->json([
                'success' => true,
                'message' => 'Absen masuk berhasil',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Absen pulang berhasil',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

        return response()->json([
            'success' => true,
            'data' => $absensis
        ]);
    }

    public function riwayat(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);

        $absensis = $user->absensis()
            ->orderBy('tanggal', 'desc')
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

        return response()->json([
            'success' => true,
            'data' => $absensis
        ]);
    }
}