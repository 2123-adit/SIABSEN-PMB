<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Statistik Umum
        $totalUsers = User::where('status', 'aktif')->where('role', 'user')->count();
        $totalAbsenHariIni = Absensi::whereDate('tanggal', $today)->count();
        $totalTerlambatHariIni = Absensi::whereDate('tanggal', $today)
            ->where('status_masuk', 'terlambat')->count();
        $totalHolidaysThisMonth = Holiday::whereMonth('tanggal', $thisMonth)
            ->whereYear('tanggal', $thisYear)
            ->where('is_active', true)->count();

        // Persentase Kehadiran Hari Ini
        $persentaseKehadiran = $totalUsers > 0 ? round(($totalAbsenHariIni / $totalUsers) * 100, 2) : 0;

        // Top 5 Karyawan Terdisiplin
        $topDisiplin = User::where('role', 'user')
            ->where('status', 'aktif')
            ->withCount(['absensis as total_hadir' => function($query) use ($thisMonth, $thisYear) {
                $query->where('status_kehadiran', 'hadir')
                      ->whereMonth('tanggal', $thisMonth)
                      ->whereYear('tanggal', $thisYear);
            }])
            ->withCount(['absensis as total_terlambat' => function($query) use ($thisMonth, $thisYear) {
                $query->where('status_masuk', 'terlambat')
                      ->whereMonth('tanggal', $thisMonth)
                      ->whereYear('tanggal', $thisYear);
            }])
            ->orderBy('total_hadir', 'desc')
            ->orderBy('total_terlambat', 'asc')
            ->limit(5)
            ->with('jabatan')
            ->get();

        // Top 5 Sering Terlambat
        $topTerlambat = User::where('role', 'user')
            ->where('status', 'aktif')
            ->withCount(['absensis as total_terlambat' => function($query) use ($thisMonth, $thisYear) {
                $query->where('status_masuk', 'terlambat')
                      ->whereMonth('tanggal', $thisMonth)
                      ->whereYear('tanggal', $thisYear);
            }])
            ->having('total_terlambat', '>', 0)
            ->orderBy('total_terlambat', 'desc')
            ->limit(5)
            ->with('jabatan')
            ->get();

        // Grafik Kehadiran 7 Hari Terakhir
        $grafikKehadiran = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $hadir = Absensi::whereDate('tanggal', $date)->count();
            $grafikKehadiran[] = [
                'tanggal' => $date->format('d/m'),
                'hari' => $date->format('l'),
                'hadir' => $hadir
            ];
        }

        // Statistik per Jabatan
        $statistikJabatan = DB::table('jabatans')
            ->leftJoin('users', 'jabatans.id', '=', 'users.jabatan_id')
            ->leftJoin('absensis', function($join) use ($thisMonth, $thisYear) {
                $join->on('users.id', '=', 'absensis.user_id')
                     ->whereMonth('absensis.tanggal', $thisMonth)
                     ->whereYear('absensis.tanggal', $thisYear);
            })
            ->where('users.status', 'aktif')
            ->where('users.role', 'user')
            ->select(
                'jabatans.nama_jabatan',
                DB::raw('COUNT(DISTINCT users.id) as total_user'),
                DB::raw('COUNT(CASE WHEN absensis.status_kehadiran = "hadir" THEN 1 END) as total_hadir'),
                DB::raw('COUNT(CASE WHEN absensis.status_masuk = "terlambat" THEN 1 END) as total_terlambat')
            )
            ->groupBy('jabatans.id', 'jabatans.nama_jabatan')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAbsenHariIni',
            'totalTerlambatHariIni',
            'totalHolidaysThisMonth',
            'persentaseKehadiran',
            'topDisiplin',
            'topTerlambat',
            'grafikKehadiran',
            'statistikJabatan'
        ));
    }
}
