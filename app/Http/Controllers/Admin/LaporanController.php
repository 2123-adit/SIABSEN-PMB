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
    /**
     * Helper method to sort users by Excel order
     */
    private function sortUsersByExcelOrder($users)
    {
        $excelOrder = [
            'ELVA ROITA SINAGA', 'RIA KURNIA SARI', 'SITI FLOWERNTA', 'YAYANG RAMADHANI',
            'SOFIANA', 'YAN FAHRI PURBA', 'ROIDAH', 'SUKASMI', 'JANTY SULAEMAN',
            'RICKY HIDAYAT', 'MUHAMAD YUSUF', 'SYAIFUL BAHRI', 'TUTI MEGAWATI',
            'DEFI SAPUTRI', 'NOVI YANTI', 'DESY WAHYUNI', 'PUTRI MAHYUNI',
            'MELDA SAFITRI', 'DEWI SARTIKA', 'SISKA REVIANA', 'DIAN SAFITRI',
            'SARI INDAH SISKA', 'WINDY CHAIRANI', 'ROHANI', 'NURPIATI',
            'ROSMAINI', 'NURMALA', 'RINI HAYATI', 'SITI AMINAH',
            'LILIS SRIWAHYUNI', 'SURAHMAN', 'ANDIKA', 'FAUZI SIREGAR',
            'DEDI KURNIAWAN', 'ILHAM', 'HUSEIN', 'SAMSUL BAHRI',
            'DEDI SAPUTRA', 'IRFAN', 'RIAN', 'SARI DAMAYANTI',
            'SISKA MAHARANI', 'INTAN SARI', 'SRI RAHAYU', 'FITRI APRIYANI',
            'ARMEN', 'AHMAD YANI'
        ];
        
        return $users->sortBy(function($user) use ($excelOrder) {
            $position = array_search($user->name, $excelOrder);
            return $position !== false ? $position : 999;
        })->values();
    }
    public function index(Request $request)
    {
        $users = $this->sortUsersByExcelOrder(User::where('role', 'user')->where('status', 'aktif')->get());
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
        
        $users = $this->sortUsersByExcelOrder($usersQuery->get());
        
        // Get ALL existing attendance records in date range (without status filter for complete coverage)
        $existingAbsensis = Absensi::with(['user', 'user.jabatan'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('user_id', $users->pluck('id'))
            ->orderBy('tanggal', 'desc')
            ->orderBy('user_id', 'asc')
            ->get();
            
        // Apply status filter only when showing filtered results  
        $filteredAbsensis = $existingAbsensis->when($request->filled('status_kehadiran'), function($collection) use ($request) {
            return $collection->where('status_kehadiran', $request->status_kehadiran);
        });
        
        // Get holidays in the date range
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('tanggal')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        $completeData = collect();
        
        // Strategy: Determine whether to show complete coverage or filtered results
        if ($request->filled('status_kehadiran')) {
            if ($request->status_kehadiran === 'alfa') {
                // When filtering for ALFA: Show complete coverage (missing days will show as ALFA virtual)
                // Continue to complete coverage logic below
            } else {
                // When filtering for specific existing status (hadir/izin/sakit): Show only existing records with that status
                return $filteredAbsensis;
            }
        }
        
        // Default (no filter) OR filtering for ALFA: Show complete attendance coverage (existing records + missing days as ALFA)
        
        // First, add all existing records to maintain their original status
        foreach ($existingAbsensis as $existingRecord) {
            $completeData->push($existingRecord);
        }
        
        // Then, create a lookup map for faster checking
        $existingLookup = $existingAbsensis->groupBy(function($absensi) {
            return $absensi->user_id . '_' . $absensi->tanggal->toDateString();
        });
        
        // Now generate virtual ALFA records for missing days only
        foreach ($users as $user) {
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                $lookupKey = $user->id . '_' . $dateString;
                $isHoliday = in_array($dateString, $holidays);
                
                // Check if user's jabatan should work on this day
                $isWorkingDay = true; // Default for backward compatibility
                if (method_exists($user->jabatan, 'isWorkingDay')) {
                    $isWorkingDay = $user->jabatan->isWorkingDay($currentDate);
                }
                
                // Skip if it's holiday or not a working day or already has record
                if ($isHoliday || !$isWorkingDay || $existingLookup->has($lookupKey)) {
                    $currentDate->addDay();
                    continue;
                }
                
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

        // Calculate start and end dates for the month
        $startDate = Carbon::create($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Statistik per jabatan
        $statistikJabatan = Jabatan::with(['users' => function($query) {
                $query->where('status', 'aktif')->where('role', 'user');
            }])
            ->get()
            ->map(function($jabatan) use ($bulan, $tahun, $startDate, $endDate) {
                $totalUser = $jabatan->users->count();
                
                if ($totalUser == 0) {
                    return [
                        'nama_jabatan' => $jabatan->nama_jabatan,
                        'total_user' => 0,
                        'total_hari_kerja' => 0,
                        'total_hadir' => 0,
                        'total_izin' => 0,
                        'total_sakit' => 0,
                        'total_alfa' => 0,
                        'total_terlambat' => 0,
                        'persentase_kehadiran' => 0
                    ];
                }

                // Calculate working days for this position in the month
                $totalHariKerja = 0;
                if ($jabatan && method_exists($jabatan, 'getWorkingDaysCount')) {
                    $totalHariKerja = $jabatan->getWorkingDaysCount($startDate, $endDate);
                } else {
                    // Default: count weekdays only (Mon-Fri)
                    $current = $startDate->copy();
                    while ($current <= $endDate) {
                        if (!in_array($current->dayOfWeek, [0, 6])) { // 0=Sunday, 6=Saturday
                            $totalHariKerja++;
                        }
                        $current->addDay();
                    }
                }

                // Total possible attendances = total users * working days
                $totalKemungkinanAbsensi = $totalUser * $totalHariKerja;

                // Get actual attendance records
                $totalHadir = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('status_kehadiran', 'hadir')
                    ->count();

                $totalIzin = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('status_kehadiran', 'izin')
                    ->count();

                $totalSakit = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('status_kehadiran', 'sakit')
                    ->count();

                $totalAlfa = Absensi::whereHas('user', function($q) use ($jabatan) {
                        $q->where('jabatan_id', $jabatan->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('status_kehadiran', 'alfa')
                    ->count();

                // Add virtual alfa (missing attendances)
                $totalRecordsAda = $totalHadir + $totalIzin + $totalSakit + $totalAlfa;
                $totalAlfaVirtual = max(0, $totalKemungkinanAbsensi - $totalRecordsAda);
                $totalAlfaSebenarnya = $totalAlfa + $totalAlfaVirtual;

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
                    'total_hari_kerja' => $totalHariKerja,
                    'total_hadir' => $totalHadir,
                    'total_izin' => $totalIzin,
                    'total_sakit' => $totalSakit,
                    'total_alfa' => $totalAlfaSebenarnya,
                    'total_terlambat' => $totalTerlambat,
                    'persentase_kehadiran' => $totalKemungkinanAbsensi > 0 ? round(($totalHadir / $totalKemungkinanAbsensi) * 100, 2) : 0
                ];
            });

        return view('admin.laporan.statistik', compact('statistikJabatan', 'bulan', 'tahun'));
    }
}