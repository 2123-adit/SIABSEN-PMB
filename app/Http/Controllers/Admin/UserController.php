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

class UserController extends Controller
{
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
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15);
        $jabatans = Jabatan::all();

        return view('admin.users.index', compact('users', 'jabatans'));
    }

    public function create()
    {
        $jabatans = Jabatan::all();
        return view('admin.users.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username|max:50|regex:/^[a-zA-Z0-9._]+$/',
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
        Log::info("User created: {$user->username} with password: {$password}");
        
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
            'username' => ['required', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', Rule::unique('users')->ignore($user->id)],
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
            Log::info("Password changed for user: {$user->username}");
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
        $newPassword = 'password123';
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->back()
            ->with('success', "Password berhasil direset menjadi: {$newPassword}");
    }
}