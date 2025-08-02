<?php
// app/Http/Controllers/Admin/GeofencingController.php - NEW

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeofencingSetting;
use Illuminate\Http\Request;

class GeofencingController extends Controller
{
    public function index()
    {
        $geofencings = GeofencingSetting::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.geofencing.index', compact('geofencings'));
    }

    public function create()
    {
        return view('admin.geofencing.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:1000',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable'
        ]);

        // Jika setting baru diaktifkan, nonaktifkan yang lain
        if ($request->has('is_active')) {
            GeofencingSetting::where('is_active', true)->update(['is_active' => false]);
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        GeofencingSetting::create($data);

        return redirect()->route('admin.geofencing.index')
            ->with('success', 'Pengaturan geofencing berhasil ditambahkan');
    }

    public function edit(GeofencingSetting $geofencing)
    {
        return view('admin.geofencing.edit', compact('geofencing'));
    }

    public function update(Request $request, GeofencingSetting $geofencing)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:1000',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable'
        ]);

        // Jika setting ini diaktifkan, nonaktifkan yang lain
        if ($request->has('is_active')) {
            GeofencingSetting::where('id', '!=', $geofencing->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $geofencing->update($data);

        return redirect()->route('admin.geofencing.index')
            ->with('success', 'Pengaturan geofencing berhasil diupdate');
    }

    public function destroy(GeofencingSetting $geofencing)
    {
        $geofencing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan geofencing berhasil dihapus'
        ]);
    }

    public function toggle(GeofencingSetting $geofencing)
    {
        if (!$geofencing->is_active) {
            // Jika akan diaktifkan, nonaktifkan yang lain
            GeofencingSetting::where('id', '!=', $geofencing->id)
                ->update(['is_active' => false]);
            $geofencing->update(['is_active' => true]);
        } else {
            $geofencing->update(['is_active' => false]);
        }

        $status = $geofencing->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'message' => "Geofencing berhasil {$status}",
            'new_status' => $geofencing->is_active
        ]);
    }

    public function testLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'geofencing_id' => 'required|exists:geofencing_settings,id'
        ]);

        $geofencing = GeofencingSetting::findOrFail($request->geofencing_id);
        $result = $geofencing->isWithinRadius($request->latitude, $request->longitude);

        return response()->json([
            'success' => true,
            'data' => [
                'is_within' => $result['is_within'],
                'distance' => $result['distance'],
                'radius' => $result['radius'],
                'location_name' => $result['location_name'],
                'status_text' => $result['is_within'] ? 'Dalam Area' : 'Luar Area',
                'status_color' => $result['is_within'] ? 'success' : 'danger'
            ]
        ]);
    }
}