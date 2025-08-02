<?php
// app/Http/Controllers/Admin/HolidayController.php - Update ini

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            if ($request->status == 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status == 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $holidays = $query->orderBy('tanggal', 'asc')->paginate(15);

        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:holidays,tanggal',
            'nama_libur' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:nasional,cuti_bersama,khusus',
            'is_active' => 'nullable'
        ]);

        $data = $request->all();
        // DEFAULT NONAKTIF untuk hari libur baru
        $data['is_active'] = $request->has('is_active') ? true : false;

        Holiday::create($data);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan (Status: Nonaktif)');
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:holidays,tanggal,' . $holiday->id,
            'nama_libur' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:nasional,cuti_bersama,khusus',
            'is_active' => 'nullable'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $holiday->update($data);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil diupdate');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil dihapus'
        ]);
    }

    public function toggle(Holiday $holiday)
    {
        $oldStatus = $holiday->is_active;
        $holiday->update(['is_active' => !$holiday->is_active]);

        $statusText = $holiday->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'message' => "Hari libur berhasil {$statusText}",
            'new_status' => $holiday->is_active,
            'status_text' => $holiday->is_active ? 'Aktif' : 'Nonaktif'
        ]);
    }
}