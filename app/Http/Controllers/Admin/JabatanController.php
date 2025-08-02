<?php
// app/Http/Controllers/Admin/JabatanController.php - NEW CONTROLLER

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jabatan::withCount(['users as total_active_users' => function($q) {
            $q->where('status', 'aktif')->where('role', 'user');
        }]);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_jabatan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $jabatans = $query->orderBy('nama_jabatan')->paginate(15);

        return view('admin.jabatan.index', compact('jabatans'));
    }

    public function create()
    {
        return view('admin.jabatan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
            'deskripsi' => 'nullable|string',
            'toleransi_terlambat' => 'required|integer|min:0|max:60',
            'jadwal_kerja' => 'required|array',
            'jadwal_kerja.*' => 'boolean',
            'keterangan_jadwal' => 'nullable|string|max:255'
        ]);

        // Process jadwal_kerja - convert checkbox array to proper format
        $jadwalKerja = [
            'senin' => $request->has('jadwal_kerja.senin'),
            'selasa' => $request->has('jadwal_kerja.selasa'),
            'rabu' => $request->has('jadwal_kerja.rabu'),
            'kamis' => $request->has('jadwal_kerja.kamis'),
            'jumat' => $request->has('jadwal_kerja.jumat'),
            'sabtu' => $request->has('jadwal_kerja.sabtu'),
            'minggu' => $request->has('jadwal_kerja.minggu'),
        ];

        $data = $request->all();
        $data['jadwal_kerja'] = $jadwalKerja;

        Jabatan::create($data);

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function show(Jabatan $jabatan)
    {
        $jabatan->load(['users' => function($query) {
            $query->where('role', 'user')->with('absensis');
        }]);

        // Get statistics
        $totalUsers = $jabatan->users->count();
        $activeUsers = $jabatan->users->where('status', 'aktif')->count();
        $inactiveUsers = $jabatan->users->where('status', 'nonaktif')->count();

        // Monthly attendance statistics
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $monthlyStats = [
            'total_hadir' => $jabatan->users->sum(function($user) use ($currentMonth, $currentYear) {
                return $user->absensis()
                    ->where('status_kehadiran', 'hadir')
                    ->whereMonth('tanggal', $currentMonth)
                    ->whereYear('tanggal', $currentYear)
                    ->count();
            }),
            'total_terlambat' => $jabatan->users->sum(function($user) use ($currentMonth, $currentYear) {
                return $user->absensis()
                    ->where('status_masuk', 'terlambat')
                    ->whereMonth('tanggal', $currentMonth)
                    ->whereYear('tanggal', $currentYear)
                    ->count();
            })
        ];

        return view('admin.jabatan.show', compact('jabatan', 'totalUsers', 'activeUsers', 'inactiveUsers', 'monthlyStats'));
    }

    public function edit(Jabatan $jabatan)
    {
        return view('admin.jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'nama_jabatan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jabatans', 'nama_jabatan')->ignore($jabatan->id)
            ],
            'deskripsi' => 'nullable|string',
            'toleransi_terlambat' => 'required|integer|min:0|max:60',
            'jadwal_kerja' => 'required|array',
            'keterangan_jadwal' => 'nullable|string|max:255'
        ]);

        // Process jadwal_kerja
        $jadwalKerja = [
            'senin' => $request->has('jadwal_kerja.senin'),
            'selasa' => $request->has('jadwal_kerja.selasa'),
            'rabu' => $request->has('jadwal_kerja.rabu'),
            'kamis' => $request->has('jadwal_kerja.kamis'),
            'jumat' => $request->has('jadwal_kerja.jumat'),
            'sabtu' => $request->has('jadwal_kerja.sabtu'),
            'minggu' => $request->has('jadwal_kerja.minggu'),
        ];

        $data = $request->all();
        $data['jadwal_kerja'] = $jadwalKerja;

        $jabatan->update($data);

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil diupdate');
    }

    public function destroy(Jabatan $jabatan)
    {
        // Check if jabatan has users
        $userCount = $jabatan->users()->count();
        
        if ($userCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menghapus jabatan ini karena masih memiliki {$userCount} user. Pindahkan user ke jabatan lain terlebih dahulu."
            ], 422);
        }

        $jabatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil dihapus'
        ]);
    }

    /**
     * Get users by jabatan (AJAX)
     */
    public function getUsers(Jabatan $jabatan)
    {
        $users = $jabatan->users()
            ->where('role', 'user')
            ->select('id', 'username', 'name', 'status', 'jam_masuk', 'jam_pulang')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Bulk move users to different jabatan
     */
    public function bulkMoveUsers(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'new_jabatan_id' => 'required|exists:jabatans,id|different:' . $jabatan->id,
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        $newJabatan = Jabatan::findOrFail($request->new_jabatan_id);
        
        // Move users
        User::whereIn('id', $request->user_ids)
            ->where('jabatan_id', $jabatan->id)
            ->update(['jabatan_id' => $request->new_jabatan_id]);

        $movedCount = count($request->user_ids);

        return response()->json([
            'success' => true,
            'message' => "Berhasil memindahkan {$movedCount} user ke jabatan {$newJabatan->nama_jabatan}"
        ]);
    }
}