<?php

namespace App\Http\Controllers\Api;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required', // CHANGED: username -> user_id
            'password' => 'required',
        ]);

        $user = User::withJabatan() // USING SCOPE
                   ->activeUsers() // USING SCOPE  
                   ->regularUsers() // USING SCOPE
                   ->where('user_id', $request->user_id) // CHANGED: username -> user_id
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                'User ID atau password salah',
                'INVALID_CREDENTIALS',
                401
            );
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'user_id' => $user->user_id, // CHANGED: username -> user_id
                'name' => $user->name,
                'jabatan' => $user->jabatan->nama_jabatan,
                'jam_masuk' => $user->jam_masuk,
                'jam_pulang' => $user->jam_pulang,
                'password_changed' => $user->password_changed,
                'foto_profil' => $user->foto_profil ? asset('storage/' . $user->foto_profil) : null
            ],
            'token' => $token
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil');
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('jabatan');

        return $this->successResponse([
            'id' => $user->id,
            'user_id' => $user->user_id, // CHANGED: username -> user_id
            'name' => $user->name,
            'jabatan' => $user->jabatan->nama_jabatan,
            'jam_masuk' => $user->jam_masuk,
            'jam_pulang' => $user->jam_pulang,
            'persentase_kehadiran' => $user->persentase_kehadiran,
            'total_terlambat' => $user->total_terlambat,
            'foto_profil' => $user->foto_profil ? asset('storage/' . $user->foto_profil) : null
        ], 'Data profil berhasil diambil');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required'
        ]);

        $user = $request->user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse(
                'Password lama tidak sesuai',
                'INVALID_CURRENT_PASSWORD',
                400
            );
        }

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->password_changed = true;
        $user->password_changed_at = now();
        $user->save();

        return $this->successResponse(null, 'Password berhasil diubah');
    }

    public function serverTime()
    {
        return $this->successResponse([
            'server_time' => now('Asia/Jakarta')->toISOString(),
            'timezone' => 'Asia/Jakarta',
            'formatted' => now('Asia/Jakarta')->format('Y-m-d H:i:s')
        ], 'Server time berhasil diambil');
    }

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user = $request->user();

        try {
            // Hapus foto lama jika ada
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            // Upload foto baru
            $fotoPath = $request->file('foto_profil')->store('profile-photos', 'public');
            
            // Update user
            $user->foto_profil = $fotoPath;
            $user->save();

            return $this->successResponse([
                'foto_profil' => asset('storage/' . $fotoPath)
            ], 'Foto profil berhasil diupload');

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal mengupload foto profil: ' . $e->getMessage(),
                'UPLOAD_ERROR',
                500
            );
        }
    }
}