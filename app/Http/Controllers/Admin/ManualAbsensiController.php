<?php
// app/Http/Controllers/Admin/ManualAbsensiController.php - FIXED

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ManualAbsensiController extends Controller
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
        $query = Absensi::with(['user', 'user.jabatan']);

        // Filter
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('jabatan_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('jabatan_id', $request->jabatan_id);
            });
        }

        $absensis = $query->orderBy('tanggal', 'desc')
                         ->orderBy('jam_masuk', 'desc')
                         ->paginate(20);

        $users = $this->sortUsersByExcelOrder(User::where('role', 'user')->where('status', 'aktif')->get());
        $jabatans = Jabatan::all();

        return view('admin.manual-absensi.index', compact('absensis', 'users', 'jabatans'));
    }

    public function create()
    {
        $users = $this->sortUsersByExcelOrder(User::where('role', 'user')->where('status', 'aktif')->with('jabatan')->get());
        $jabatans = Jabatan::all();
        
        return view('admin.manual-absensi.create', compact('users', 'jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alfa',
            'keterangan' => 'nullable|string|max:500',
            'foto_masuk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto_pulang' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Cek apakah sudah ada absensi untuk tanggal tersebut
        $existingAbsensi = Absensi::where('user_id', $request->user_id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if ($existingAbsensi) {
            return back()->withErrors(['tanggal' => 'Absensi untuk tanggal ini sudah ada']);
        }

        // Cek hari libur
        $isHoliday = Holiday::isHoliday($request->tanggal);
        if ($isHoliday && $request->status_kehadiran == 'hadir') {
            return back()->withErrors(['tanggal' => 'Tidak dapat menambah absensi hadir pada hari libur']);
        }

        $user = User::findOrFail($request->user_id);
        
        // Parse tanggal dengan timezone Asia/Jakarta
        $tanggal = Carbon::parse($request->tanggal)->setTimezone('Asia/Jakarta');
        
        $data = [
            'user_id' => $request->user_id,
            'tanggal' => $tanggal->format('Y-m-d'),
            'status_kehadiran' => $request->status_kehadiran,
            'keterangan' => $request->keterangan,
            'menit_terlambat' => 0,
            'source' => 'manual'
        ];

        // FIXED: Handle jam masuk dengan proper datetime dan type casting
        if ($request->filled('jam_masuk') && $request->status_kehadiran == 'hadir') {
            // Parse jam masuk
            $jamMasukParts = explode(':', $request->jam_masuk);
            $jamMasukDateTime = $tanggal->copy()
                ->setTime((int)$jamMasukParts[0], (int)$jamMasukParts[1], 0);
                
            // Parse jam kerja mulai
            $jamKerjaMulai = $tanggal->copy()
                ->setTime($user->jam_masuk->hour, $user->jam_masuk->minute, 0);
                
            // FIXED: Cast toleransi to integer to prevent Carbon error
            $toleransi = (int)($user->jabatan->toleransi_terlambat ?? 15);

            $data['jam_masuk'] = $jamMasukDateTime->format('H:i:s');
            
            // Hitung keterlambatan
            if ($jamMasukDateTime->isAfter($jamKerjaMulai->copy()->addMinutes($toleransi))) {
                $data['status_masuk'] = 'terlambat';
                $data['menit_terlambat'] = $jamMasukDateTime->diffInMinutes($jamKerjaMulai);
            } else {
                $data['status_masuk'] = 'tepat_waktu';
            }
        }

        // FIXED: Handle jam pulang dengan proper datetime
        if ($request->filled('jam_pulang') && $request->status_kehadiran == 'hadir') {
            // Parse jam pulang
            $jamPulangParts = explode(':', $request->jam_pulang);
            $jamPulangDateTime = $tanggal->copy()
                ->setTime((int)$jamPulangParts[0], (int)$jamPulangParts[1], 0);
                
            // Parse jam kerja berakhir
            $jamKerjaBerakhir = $tanggal->copy()
                ->setTime($user->jam_pulang->hour, $user->jam_pulang->minute, 0);
            
            // Handle shift malam (jam pulang hari berikutnya)
            if ($user->jam_pulang->hour < $user->jam_masuk->hour) {
                $jamKerjaBerakhir->addDay();
                // Jika jam pulang input < jam masuk, anggap hari berikutnya
                if ((int)$jamPulangParts[0] < $user->jam_masuk->hour) {
                    $jamPulangDateTime->addDay();
                }
            }

            $data['jam_pulang'] = $jamPulangDateTime->format('H:i:s');
            
            if ($jamPulangDateTime->isBefore($jamKerjaBerakhir)) {
                $data['status_pulang'] = 'lebih_awal';
            } else {
                $data['status_pulang'] = 'tepat_waktu';
            }
        }

        // Handle foto masuk
        if ($request->hasFile('foto_masuk')) {
            $data['foto_masuk'] = $request->file('foto_masuk')->store('absensi/manual/masuk/' . $tanggal->format('Y/m'), 'public');
        }

        // Handle foto pulang
        if ($request->hasFile('foto_pulang')) {
            $data['foto_pulang'] = $request->file('foto_pulang')->store('absensi/manual/pulang/' . $tanggal->format('Y/m'), 'public');
        }

        // Default geofencing untuk manual entry
        $data['is_within_geofence_masuk'] = true;
        $data['is_within_geofence_pulang'] = true;
        $data['distance_from_office_masuk'] = 0;
        $data['distance_from_office_pulang'] = 0;

        Absensi::create($data);

        return redirect()->route('admin.manual-absensi.index')
            ->with('success', 'Data absensi berhasil ditambahkan');
    }

    public function edit(Absensi $absensi)
    {
        $users = $this->sortUsersByExcelOrder(User::where('role', 'user')->where('status', 'aktif')->with('jabatan')->get());
        $jabatans = Jabatan::all();
        
        return view('admin.manual-absensi.edit', compact('absensi', 'users', 'jabatans'));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alfa',
            'keterangan' => 'nullable|string|max:500',
            'foto_masuk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto_pulang' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Cek apakah sudah ada absensi lain untuk tanggal tersebut (kecuali yang sedang diedit)
        $existingAbsensi = Absensi::where('user_id', $request->user_id)
            ->whereDate('tanggal', $request->tanggal)
            ->where('id', '!=', $absensi->id)
            ->first();

        if ($existingAbsensi) {
            return back()->withErrors(['tanggal' => 'Absensi untuk tanggal ini sudah ada']);
        }

        $user = User::findOrFail($request->user_id);
        
        // Parse tanggal dengan timezone Asia/Jakarta
        $tanggal = Carbon::parse($request->tanggal)->setTimezone('Asia/Jakarta');
        
        $data = [
            'user_id' => $request->user_id,
            'tanggal' => $tanggal->format('Y-m-d'),
            'status_kehadiran' => $request->status_kehadiran,
            'keterangan' => $request->keterangan,
            'menit_terlambat' => 0,
            'jam_masuk' => null,
            'jam_pulang' => null,
            'status_masuk' => null,
            'status_pulang' => null,
            'source' => $absensi->source ?? 'manual'
        ];

        // FIXED: Handle jam masuk dengan proper datetime dan type casting
        if ($request->filled('jam_masuk') && $request->status_kehadiran == 'hadir') {
            $jamMasukParts = explode(':', $request->jam_masuk);
            $jamMasukDateTime = $tanggal->copy()
                ->setTime((int)$jamMasukParts[0], (int)$jamMasukParts[1], 0);
                
            $jamKerjaMulai = $tanggal->copy()
                ->setTime($user->jam_masuk->hour, $user->jam_masuk->minute, 0);
                
            // FIXED: Cast toleransi to integer to prevent Carbon error
            $toleransi = (int)($user->jabatan->toleransi_terlambat ?? 15);

            $data['jam_masuk'] = $jamMasukDateTime->format('H:i:s');
            
            if ($jamMasukDateTime->isAfter($jamKerjaMulai->copy()->addMinutes($toleransi))) {
                $data['status_masuk'] = 'terlambat';
                $data['menit_terlambat'] = $jamMasukDateTime->diffInMinutes($jamKerjaMulai);
            } else {
                $data['status_masuk'] = 'tepat_waktu';
            }
        }

        // FIXED: Handle jam pulang dengan proper datetime
        if ($request->filled('jam_pulang') && $request->status_kehadiran == 'hadir') {
            $jamPulangParts = explode(':', $request->jam_pulang);
            $jamPulangDateTime = $tanggal->copy()
                ->setTime((int)$jamPulangParts[0], (int)$jamPulangParts[1], 0);
                
            $jamKerjaBerakhir = $tanggal->copy()
                ->setTime($user->jam_pulang->hour, $user->jam_pulang->minute, 0);
            
            // Handle shift malam
            if ($user->jam_pulang->hour < $user->jam_masuk->hour) {
                $jamKerjaBerakhir->addDay();
                if ((int)$jamPulangParts[0] < $user->jam_masuk->hour) {
                    $jamPulangDateTime->addDay();
                }
            }

            $data['jam_pulang'] = $jamPulangDateTime->format('H:i:s');
            
            if ($jamPulangDateTime->isBefore($jamKerjaBerakhir)) {
                $data['status_pulang'] = 'lebih_awal';
            } else {
                $data['status_pulang'] = 'tepat_waktu';
            }
        }

        // Handle foto masuk
        if ($request->hasFile('foto_masuk')) {
            // Hapus foto lama
            if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
                Storage::disk('public')->delete($absensi->foto_masuk);
            }
            $data['foto_masuk'] = $request->file('foto_masuk')->store('absensi/manual/masuk/' . $tanggal->format('Y/m'), 'public');
        }

        // Handle foto pulang
        if ($request->hasFile('foto_pulang')) {
            // Hapus foto lama
            if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
                Storage::disk('public')->delete($absensi->foto_pulang);
            }
            $data['foto_pulang'] = $request->file('foto_pulang')->store('absensi/manual/pulang/' . $tanggal->format('Y/m'), 'public');
        }

        $absensi->update($data);

        return redirect()->route('admin.manual-absensi.index')
            ->with('success', 'Data absensi berhasil diupdate');
    }

    public function destroy(Absensi $absensi)
    {
        // Hapus foto jika ada
        if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
            Storage::disk('public')->delete($absensi->foto_masuk);
        }
        if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
            Storage::disk('public')->delete($absensi->foto_pulang);
        }

        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus'
        ]);
    }

    public function bulkCreate()
    {
        $users = $this->sortUsersByExcelOrder(User::where('role', 'user')->where('status', 'aktif')->with('jabatan')->get());
        $jabatans = Jabatan::all();
        
        return view('admin.manual-absensi.bulk-create', compact('users', 'jabatans'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alfa',
            'keterangan' => 'nullable|string|max:500'
        ]);

        $tanggal = Carbon::parse($request->tanggal)->setTimezone('Asia/Jakarta');
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($request->users as $userId) {
            // Cek apakah sudah ada absensi
            $existingAbsensi = Absensi::where('user_id', $userId)
                ->whereDate('tanggal', $request->tanggal)
                ->first();

            if ($existingAbsensi) {
                $user = User::find($userId);
                $errors[] = "Absensi {$user->name} untuk tanggal ini sudah ada";
                $errorCount++;
                continue;
            }

            $user = User::find($userId);

            $data = [
                'user_id' => $userId,
                'tanggal' => $tanggal->format('Y-m-d'),
                'status_kehadiran' => $request->status_kehadiran,
                'keterangan' => $request->keterangan,
                'menit_terlambat' => 0,
                'is_within_geofence_masuk' => true,
                'is_within_geofence_pulang' => true,
                'distance_from_office_masuk' => 0,
                'distance_from_office_pulang' => 0,
                'source' => 'bulk'
            ];

            // FIXED: For bulk hadir, add default working hours with null check
            if ($request->status_kehadiran == 'hadir') {
                // FIXED: Add null checks for jam_masuk and jam_pulang
                if ($user->jam_masuk) {
                    $data['jam_masuk'] = $user->jam_masuk->format('H:i:s');
                    $data['status_masuk'] = 'tepat_waktu';
                }
                
                if ($user->jam_pulang) {
                    $data['jam_pulang'] = $user->jam_pulang->format('H:i:s');
                    $data['status_pulang'] = 'tepat_waktu';
                }
            }

            Absensi::create($data);
            $successCount++;
        }

        if ($errorCount > 0) {
            return back()->withErrors($errors)->with('warning', "Berhasil menambah {$successCount} absensi, {$errorCount} gagal");
        }

        return redirect()->route('admin.manual-absensi.index')
            ->with('success', "Berhasil menambah {$successCount} data absensi");
    }

    public function importTemplate()
    {
        $users = $this->sortUsersByExcelOrder(User::where('role', 'user')->where('status', 'aktif')->with('jabatan')->get());
        
        return view('admin.manual-absensi.import', compact('users'));
    }

    public function getUserByJabatan(Request $request)
    {
        $users = User::where('jabatan_id', $request->jabatan_id)
            ->where('role', 'user')
            ->where('status', 'aktif')
            ->get(['id', 'name', 'username']);

        return response()->json($users);
    }
}