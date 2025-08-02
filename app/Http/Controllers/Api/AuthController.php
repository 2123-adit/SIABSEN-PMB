<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required', // CHANGED: nik -> username
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username) // CHANGED: nik -> username
                   ->where('status', 'aktif')
                   ->where('role', 'user')
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah' // CHANGED: NIK -> Username
            ], 401);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username, // CHANGED: nik -> username
                    'name' => $user->name,
                    'jabatan' => $user->jabatan->nama_jabatan,
                    'jam_masuk' => $user->jam_masuk,
                    'jam_pulang' => $user->jam_pulang
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('jabatan');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username, // CHANGED: nik -> username
                'name' => $user->name,
                'jabatan' => $user->jabatan->nama_jabatan,
                'jam_masuk' => $user->jam_masuk,
                'jam_pulang' => $user->jam_pulang,
                'persentase_kehadiran' => $user->persentase_kehadiran,
                'total_terlambat' => $user->total_terlambat
            ]
        ]);
    }

    public function serverTime()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => now('Asia/Jakarta')->toISOString(),
                'timezone' => 'Asia/Jakarta',
                'formatted' => now('Asia/Jakarta')->format('Y-m-d H:i:s')
            ]
        ]);
    }
}