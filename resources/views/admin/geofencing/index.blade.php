@extends('layouts.admin')

@section('title', 'Pengaturan Geofencing - ADMA Absensi Kantor')
@section('page-title', 'Pengaturan Geofencing')

@push('styles')
<style>
    .geofence-card {
        transition: transform 0.2s;
        min-height: 280px;
    }
    .geofence-card:hover {
        transform: translateY(-2px);
    }
    .active-geofence {
        border: 2px solid #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }
    .inactive-geofence {
        opacity: 0.7;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    Pengaturan Geofencing
                </h5>
                <a href="{{ route('admin.geofencing.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Lokasi
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Info:</strong> Geofencing membatasi user hanya bisa absen dalam radius tertentu dari lokasi yang ditentukan. 
                    Hanya satu lokasi yang bisa aktif dalam satu waktu.
                </div>

                <div class="row">
                    @forelse($geofencings as $geofencing)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card geofence-card {{ $geofencing->is_active ? 'active-geofence' : 'inactive-geofence' }}">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        @if($geofencing->is_active)
                                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        @else
                                            <i class="bi bi-circle text-muted me-1"></i>
                                        @endif
                                        {{ $geofencing->nama_lokasi }}
                                    </h6>
                                    <span class="badge bg-{{ $geofencing->status_badge }}">
                                        {{ $geofencing->status_text }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <small class="text-muted">{{ $geofencing->deskripsi ?: 'Tidak ada deskripsi' }}</small>
                                    </p>
                                    
                                    <div class="mb-2">
                                        <strong>Koordinat:</strong><br>
                                        <small>
                                            <i class="bi bi-geo-alt text-primary"></i>
                                            {{ $geofencing->latitude }}, {{ $geofencing->longitude }}
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Radius:</strong> 
                                        <span class="badge bg-info">{{ $geofencing->radius_meter }}m</span>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="https://www.google.com/maps?q={{ $geofencing->latitude }},{{ $geofencing->longitude }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-map me-1"></i>
                                            Lihat di Maps
                                        </a>
                                        
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.geofencing.edit', $geofencing) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-{{ $geofencing->is_active ? 'secondary' : 'success' }}" 
                                                    onclick="toggleStatus({{ $geofencing->id }})" 
                                                    title="{{ $geofencing->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi bi-{{ $geofencing->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="testLocation({{ $geofencing->id }})" title="Test Lokasi">
                                                <i class="bi bi-geo-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteGeofencing({{ $geofencing->id }})" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-geo-alt" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Belum ada pengaturan geofencing</h5>
                                <p>Tambahkan lokasi geofencing untuk membatasi area absensi</p>
                                <a href="{{ route('admin.geofencing.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Tambah Lokasi Pertama
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                {{ $geofencings->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Test Location Modal -->
<div class="modal fade" id="testLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Lokasi Geofencing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testLocationForm">
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control" id="testLat" 
                               placeholder="Contoh: 3.5952" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control" id="testLng" 
                               placeholder="Contoh: 98.6722" required>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-info w-100" onclick="getCurrentLocation()">
                            <i class="bi bi-geo-alt me-1"></i>
                            Gunakan Lokasi Saat Ini
                        </button>
                    </div>
                </form>
                <div id="testResult" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="submitTestLocation()">
                    <i class="bi bi-search me-1"></i>
                    Test Lokasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus pengaturan geofencing ini?<br>
                <strong>Perhatian:</strong> Jika ini adalah satu-satunya geofencing aktif, 
                maka user akan bisa absen dari mana saja.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentTestGeofencing = null;

    function toggleStatus(id) {
        if (confirm('Yakin ingin mengubah status geofencing ini?')) {
            fetch(`/admin/geofencing/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal mengubah status: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        }
    }

    function deleteGeofencing(id) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/geofencing/${id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function testLocation(id) {
        currentTestGeofencing = id;
        document.getElementById('testResult').style.display = 'none';
        document.getElementById('testLat').value = '';
        document.getElementById('testLng').value = '';
        new bootstrap.Modal(document.getElementById('testLocationModal')).show();
    }

    function getCurrentLocation() {
        const loadingBtn = event.target;
        const originalHtml = loadingBtn.innerHTML;
        loadingBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Getting location...';
        loadingBtn.disabled = true;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('testLat').value = position.coords.latitude;
                document.getElementById('testLng').value = position.coords.longitude;
                loadingBtn.innerHTML = originalHtml;
                loadingBtn.disabled = false;
            }, function(error) {
                let errorMsg = 'Gagal mendapatkan lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Permission ditolak';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Lokasi tidak tersedia';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Request timeout';
                        break;
                    default:
                        errorMsg += error.message;
                        break;
                }
                alert(errorMsg);
                loadingBtn.innerHTML = originalHtml;
                loadingBtn.disabled = false;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            });
        } else {
            alert('Geolocation tidak didukung oleh browser ini.');
            loadingBtn.innerHTML = originalHtml;
            loadingBtn.disabled = false;
        }
    }

    function submitTestLocation() {
        const lat = document.getElementById('testLat').value;
        const lng = document.getElementById('testLng').value;
        const resultDiv = document.getElementById('testResult');

        if (!lat || !lng || !currentTestGeofencing) {
            alert('Mohon isi koordinat dengan benar');
            return;
        }

        // Show loading
        resultDiv.className = 'alert alert-info';
        resultDiv.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Mengecek lokasi...';
        resultDiv.style.display = 'block';

        fetch('/admin/geofencing/test-location', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: parseFloat(lat),
                longitude: parseFloat(lng),
                geofencing_id: currentTestGeofencing
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const result = data.data;
                resultDiv.className = `alert alert-${result.status_color}`;
                resultDiv.innerHTML = `
                    <strong><i class="bi bi-${result.is_within ? 'check-circle' : 'x-circle'} me-1"></i>${result.status_text}</strong><br>
                    <small>
                        Jarak: <strong>${result.distance}m</strong> dari ${result.location_name}<br>
                        Radius maksimal: <strong>${result.radius}m</strong><br>
                        ${result.is_within ? 
                            '<i class="bi bi-check text-success"></i> Dapat melakukan absensi' : 
                            '<i class="bi bi-x text-danger"></i> Tidak dapat melakukan absensi'
                        }
                    </small>
                `;
            } else {
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Gagal melakukan test lokasi';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.className = 'alert alert-danger';
            resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Terjadi kesalahan saat test lokasi';
        });
    }
</script>
@endpush