<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Rules\StrongPassword;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends Controller
{
    /**
     * Helper method to get Excel order array
     */
    private function getExcelOrder()
    {
        return [
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
    }
    public function index(Request $request)
    {
        $query = User::with('jabatan')->where('role', 'user');

        // Filter
        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        // Order sesuai dengan urutan data di UserSeeder (Excel data)
        $excelOrder = $this->getExcelOrder();
        
        $users = $query->get()->sortBy(function($user) use ($excelOrder) {
            $position = array_search($user->name, $excelOrder);
            return $position !== false ? $position : 999; // User tidak ditemukan di urutan, taruh di akhir
        })->values();
        
        // Manual pagination
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedUsers = new LengthAwarePaginator(
            $users->slice($offset, $perPage),
            $users->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => request()->query()
            ]
        );
        $jabatans = Jabatan::all();

        return view('admin.users.index', [
            'users' => $paginatedUsers,
            'jabatans' => $jabatans
        ]);
    }

    public function create()
    {
        $jabatans = Jabatan::all();
        return view('admin.users.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:users,user_id|max:50|regex:/^[a-zA-Z0-9._]+$/',
            'name' => 'required|string|max:255',
            'password' => ['required', 'min:8', new StrongPassword()], // UPDATED
            'jabatan_id' => 'required|exists:jabatans,id',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ]);
        
        // Generate strong password if not provided
        $password = $request->password ?? Str::random(12) . '@' . rand(10, 99);
        
        $data = $request->all();
        $data['password'] = Hash::make($password);
        $user = User::create($data);
        
        // Log password for first-time setup (remove in production)
        Log::info("User created: {$user->user_id} with password: {$password}");
        
        return redirect()->route('admin.users.index')
            ->with('success', "User berhasil ditambahkan. Password: {$password}");
    }

    public function show(User $user)
    {
        $user->load('jabatan', 'absensis');
        $statistik = [
            'total_hadir' => $user->absensis()->where('status_kehadiran', 'hadir')->count(),
            'total_izin' => $user->absensis()->where('status_kehadiran', 'izin')->count(),
            'total_sakit' => $user->absensis()->where('status_kehadiran', 'sakit')->count(),
            'total_alfa' => $user->absensis()->where('status_kehadiran', 'alfa')->count(),
            'total_terlambat' => $user->absensis()->where('status_masuk', 'terlambat')->count(),
        ];

        return view('admin.users.show', compact('user', 'statistik'));
    }

    public function edit(User $user)
    {
        $jabatans = Jabatan::all();
        return view('admin.users.edit', compact('user', 'jabatans'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'user_id' => ['required', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'password' => ['nullable', 'min:8', new StrongPassword()], // UPDATED
            'jabatan_id' => 'required|exists:jabatans,id',
            'status' => 'required|in:aktif,nonaktif',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            
            // Log password change
            Log::info("Password changed for user: {$user->user_id}");
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus');
    }

    public function resetPassword(User $user)
    {
        $newPassword = $user->user_id; // Reset ke user_id sebagai password default
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->back()
            ->with('success', "Password berhasil direset menjadi: {$newPassword}");
    }
}