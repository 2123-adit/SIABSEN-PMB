<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['user', 'user.jabatan']);

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        // Filter user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter jabatan
        if ($request->filled('jabatan_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('jabatan_id', $request->jabatan_id);
            });
        }

        // Filter status
        if ($request->filled('status_kehadiran')) {
            $query->where('status_kehadiran', $request->status_kehadiran);
        }

        if ($request->filled('status_masuk')) {
            $query->where('status_masuk', $request->status_masuk);
        }

        $absensis = $query->orderBy('tanggal', 'desc')
                         ->orderBy('jam_masuk', 'desc')
                         ->paginate(20);

        $users = User::where('role', 'user')->where('status', 'aktif')->get();
        $jabatans = Jabatan::all();

        return view('admin.absensi.index', compact('absensis', 'users', 'jabatans'));
    }

    public function show(Absensi $absensi)
    {
        $absensi->load(['user', 'user.jabatan']);
        return view('admin.absensi.show', compact('absensi'));
    }

    public function edit(Absensi $absensi)
    {
        $absensi->load(['user', 'user.jabatan']);
        $users = User::where('role', 'user')->where('status', 'aktif')->with('jabatan')->get();
        $jabatans = Jabatan::all();
        
        return view('admin.absensi.edit', compact('absensi', 'users', 'jabatans'));
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

        // Cek duplikasi
        $existingAbsensi = Absensi::where('user_id', $request->user_id)
            ->whereDate('tanggal', $request->tanggal)
            ->where('id', '!=', $absensi->id)
            ->first();

        if ($existingAbsensi) {
            return back()->withErrors(['tanggal' => 'Absensi untuk tanggal ini sudah ada']);
        }

        $user = User::findOrFail($request->user_id);
        
        $data = [
            'user_id' => $request->user_id,
            'tanggal' => $request->tanggal,
            'status_kehadiran' => $request->status_kehadiran,
            'keterangan' => $request->keterangan,
            'menit_terlambat' => 0,
            'jam_masuk' => null,
            'jam_pulang' => null,
            'status_masuk' => null,
            'status_pulang' => null
        ];

        // Handle jam masuk
        if ($request->filled('jam_masuk') && $request->status_kehadiran == 'hadir') {
            $data['jam_masuk'] = $request->jam_masuk . ':00';
            
            // Hitung keterlambatan
            $jamMasukTime = Carbon::createFromFormat('H:i', $request->jam_masuk);
            $jamKerjaTime = Carbon::createFromFormat('H:i', $user->jam_masuk->format('H:i'));
            $toleransi = $user->jabatan->toleransi_terlambat ?? 15;

            if ($jamMasukTime->isAfter($jamKerjaTime->copy()->addMinutes($toleransi))) {
                $data['status_masuk'] = 'terlambat';
                $data['menit_terlambat'] = $jamMasukTime->diffInMinutes($jamKerjaTime);
            } else {
                $data['status_masuk'] = 'tepat_waktu';
            }
        }

        // Handle jam pulang
        if ($request->filled('jam_pulang') && $request->status_kehadiran == 'hadir') {
            $data['jam_pulang'] = $request->jam_pulang . ':00';
            
            $jamPulangTime = Carbon::createFromFormat('H:i', $request->jam_pulang);
            $jamKerjaPulangTime = Carbon::createFromFormat('H:i', $user->jam_pulang->format('H:i'));

            if ($jamPulangTime->isBefore($jamKerjaPulangTime)) {
                $data['status_pulang'] = 'lebih_awal';
            } else {
                $data['status_pulang'] = 'tepat_waktu';
            }
        }

        // Handle foto masuk
        if ($request->hasFile('foto_masuk')) {
            if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
                Storage::disk('public')->delete($absensi->foto_masuk);
            }
            $data['foto_masuk'] = $request->file('foto_masuk')->store('absensi/edit/masuk/' . date('Y/m'), 'public');
        }

        // Handle foto pulang
        if ($request->hasFile('foto_pulang')) {
            if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
                Storage::disk('public')->delete($absensi->foto_pulang);
            }
            $data['foto_pulang'] = $request->file('foto_pulang')->store('absensi/edit/pulang/' . date('Y/m'), 'public');
        }

        // Maintain geofencing data
        $data['is_within_geofence_masuk'] = $absensi->is_within_geofence_masuk ?? true;
        $data['is_within_geofence_pulang'] = $absensi->is_within_geofence_pulang ?? true;
        $data['distance_from_office_masuk'] = $absensi->distance_from_office_masuk ?? 0;
        $data['distance_from_office_pulang'] = $absensi->distance_from_office_pulang ?? 0;

        $absensi->update($data);

        return redirect()->route('admin.absensi.index')
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

    public function calendar(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $absensis = Absensi::with(['user'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('tanggal');

        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $calendar[$dateString] = [
                'date' => $currentDate->copy(),
                'absensis' => $absensis->get($dateString, collect()),
                'total_hadir' => $absensis->get($dateString, collect())->count(),
                'total_terlambat' => $absensis->get($dateString, collect())
                    ->where('status_masuk', 'terlambat')->count()
            ];
            $currentDate->addDay();
        }

        return view('admin.absensi.calendar', compact('calendar', 'month', 'year'));
    }
}