<?php
// app/Http/Controllers/Admin/LaporanController.php - FIXED V2

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Holiday;
use App\Exports\AbsensiExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('role', 'user')->where('status', 'aktif')->get();
        $jabatans = Jabatan::all();
        
        return view('admin.laporan.index', compact('users', 'jabatans'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'format' => 'required|in:pdf,excel',
        ]);

        // FIXED: Generate complete attendance data
        $completeAttendanceData = $this->generateCompleteAttendanceData($request);

        if ($request->format === 'excel') {
            return Excel::download(
                new AbsensiExport($completeAttendanceData, $request->all()),
                'laporan-absensi-' . Carbon::now()->format('Y-m-d') . '.xlsx'
            );
        }

        // PDF Export
        $pdf = Pdf::loadView('admin.laporan.pdf', [
            'absensis' => $completeAttendanceData,
            'filters' => $request->all(),
            'periode' => Carbon::parse($request->tanggal_mulai)->format('d/m/Y') . ' - ' . 
                        Carbon::parse($request->tanggal_selesai)->format('d/m/Y')
        ]);

        return $pdf->download('laporan-absensi-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    /**
     * FIXED: Generate complete attendance data - proper mapping
     */
    private function generateCompleteAttendanceData(Request $request)
    {
        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_selesai);
        
        // Get users based on filters
        $usersQuery = User::where('role', 'user')->where('status', 'aktif')->with('jabatan');
        
        if ($request->filled('user_id')) {
            $usersQuery->where('id', $request->user_id);
        }
        
        if ($request->filled('jabatan_id')) {
            $usersQuery->where('jabatan_id', $request->jabatan_id);
        }
        
        $users = $usersQuery->get();
        
        // Get ALL existing attendance records in date range
        $existingAbsensis = Absensi::with(['user', 'user.jabatan'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('user_id', $users->pluck('id'))
            ->when($request->filled('status_kehadiran'), function($query) use ($request) {
                return $query->where('status_kehadiran', $request->status_kehadiran);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('user_id', 'asc')
            ->get();
        
        // Get holidays in the date range
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('tanggal')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        $completeData = collect();
        
        // Strategy 1: If we want to show ONLY existing records (like current behavior)
        if ($request->filled('status_kehadiran') || !$request->has('include_missing')) {
            // Return only existing attendance records (filtered if needed)
            return $existingAbsensis;
        }
        
        // Strategy 2: If we want to show ALL expected attendance (including missing)
        foreach ($users as $user) {
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                $isHoliday = in_array($dateString, $holidays);
                
                // Check if user's jabatan should work on this day
                $isWorkingDay = true; // Default for backward compatibility
                if (method_exists($user->jabatan, 'isWorkingDay')) {
                    $isWorkingDay = $user->jabatan->isWorkingDay($currentDate);
                }
                
                // Skip if it's holiday or not a working day
                if ($isHoliday || !$isWorkingDay) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Look for existing attendance record
                $existingRecord = $existingAbsensis->first(function($absensi) use ($user, $dateString) {
                    return $absensi->user_id == $user->id && 
                           $absensi->tanggal->format('Y-m-d') == $dateString;
                });
                
                if ($existingRecord) {
                    // Add existing record
                    $completeData->push($existingRecord);
                } else {
                    // Create virtual "Alfa" record for missing attendance
                    $virtualAbsensi = new Absensi([
                        'user_id' => $user->id,
                        'tanggal' => $currentDate->copy(),
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'status_kehadiran' => 'alfa',
                        'status_masuk' => null,
                        'status_pulang' => null,
                        'menit_terlambat' => 0,
                        'keterangan' => 'Tidak melakukan absensi',
                        'foto_masuk' => null,
                        'foto_pulang' => null,
                        'latitude_masuk' => null,
                        'longitude_masuk' => null,
                        'latitude_pulang' => null,
                        'longitude_pulang' => null,
                        'is_within_geofence_masuk' => null,
                        'is_within_geofence_pulang' => null,
                        'distance_from_office_masuk' => null,
                        'distance_from_office_pulang' => null,
                        'source' => 'system_generated'
                    ]);
                    
                    // Set relationship manually for virtual record
                    $virtualAbsensi->setRelation('user', $user);
                    $virtualAbsensi->exists = false; // Mark as virtual
                    
                    $completeData->push($virtualAbsensi);
                }
                
                $currentDate->addDay();
            }
        }
        
        // Sort by date desc, then by name
        return $completeData->sortBy([
            ['tanggal', 'desc'],
            ['user.name', 'asc']
        ])->values();
    }

    public function slip(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020'
        ]);

        $user = User::with('jabatan')->findOrFail($request->user_id);
        
        $absensis = Absensi::where('user_id', $request->user_id)
            ->whereMonth('tanggal', $request->bulan)
            ->whereYear('tanggal', $request->tahun)
            ->orderBy('tanggal')
            ->get();

        $statistik = [
            'total_hadir' => $absensis->where('status_kehadiran', 'hadir')->count(),
            'total_izin' => $absensis->where('status_kehadiran', 'izin')->count(),
            'total_sakit' => $absensis->where('status_kehadiran', 'sakit')->count(),
            'total_alfa' => $absensis->where('status_kehadiran', 'alfa')->count(),
            'total_terlambat' => $absensis->where('status_masuk', 'terlambat')->count(),
        ];

        $periode = Carbon::createFromDate($request->tahun, $request->bulan, 1)
            ->format('F Y');

        $pdf = Pdf::loadView('admin.laporan.slip', compact(
            'user', 'absensis', 'statistik', 'periode'
        ));

        return $pdf->download("slip-absensi-{$user->name}-{$periode}.pdf");
    }

    public function statistik(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        // Statistik per jabatan
        $statistikJabatan = Jabatan::with(['users' => function($query) {
                $query->where('status', 'aktif')->where('role', 'user');
            }])
            ->get()
            ->map(function($jabatan) use ($bulan, $tahun) {
                $totalUser = $jabatan->users->count();
                
                $totalAbsensi = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->count();

                $totalHadir = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('status_kehadiran', 'hadir')
                    ->count();

                $totalTerlambat = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('status_masuk', 'terlambat')
                    ->count();

                return [
                    'nama_jabatan' => $jabatan->nama_jabatan,
                    'total_user' => $totalUser,
                    'total_absensi' => $totalAbsensi,
                    'total_hadir' => $totalHadir,
                    'total_terlambat' => $totalTerlambat,
                    'persentase_kehadiran' => $totalAbsensi > 0 ? round(($totalHadir / $totalAbsensi) * 100, 2) : 0
                ];
            });

        return view('admin.laporan.statistik', compact('statistikJabatan', 'bulan', 'tahun'));
    }
}