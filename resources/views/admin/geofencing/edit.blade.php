@extends('layouts.admin')

@section('title', 'Edit Geofencing - ADMA Absensi Kantor')
@section('page-title', 'Edit Pengaturan Geofencing')

@push('styles')
<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }
    .coordinate-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        border: 1px solid #e9ecef;
    }
    .map-fallback {
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
    }
    .current-status {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 1px solid #2196f3;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Edit Pengaturan Geofencing
                </h5>
            </div>
            <div class="card-body">
                <!-- Current Status Info -->
                <div class="current-status">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Status Saat Ini
                            </h6>
                            <span class="badge bg-{{ $geofencing->status_badge }} me-2">
                                {{ $geofencing->status_text }}
                            </span>
                            <small class="text-muted">
                                Dibuat: {{ $geofencing->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <a href="https://www.google.com/maps?q={{ $geofencing->latitude }},{{ $geofencing->longitude }}" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-map me-1"></i>
                                Lihat di Maps
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Perubahan koordinat atau radius akan mempengaruhi area absensi yang diizinkan.
                    Pastikan pengaturan sudah benar sebelum menyimpan.
                </div>

                <form action="{{ route('admin.geofencing.update', $geofencing) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lokasi" class="form-control @error('nama_lokasi') is-invalid @enderror" 
                                       value="{{ old('nama_lokasi', $geofencing->nama_lokasi) }}" 
                                       placeholder="Contoh: Kantor ADMA Medan" required>
                                @error('nama_lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                                <input type="number" name="radius_meter" class="form-control @error('radius_meter') is-invalid @enderror" 
                                       value="{{ old('radius_meter', $geofencing->radius_meter) }}" min="10" max="1000" required>
                                @error('radius_meter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Radius saat ini: <strong>{{ $geofencing->radius_meter }}m</strong>. 
                                    Range: 10-1000m
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="latitude" id="latitude" 
                                       class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude', $geofencing->latitude) }}" required>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Saat ini: <strong>{{ $geofencing->latitude }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="longitude" id="longitude" 
                                       class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude', $geofencing->longitude) }}" required>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Saat ini: <strong>{{ $geofencing->longitude }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                  rows="3" placeholder="Deskripsi lokasi geofencing (opsional)">{{ old('deskripsi', $geofencing->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="coordinate-info">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="bi bi-map me-1"></i>
                                Pilih Lokasi di Peta
                            </h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="resetToOriginal()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Reset
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="getCurrentLocation()">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    Lokasi Saya
                                </button>
                            </div>
                        </div>
                        
                        <!-- Map Container -->
                        <div id="mapContainer">
                            <div id="map"></div>
                            <div id="mapFallback" class="map-fallback" style="display: none;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-geo-alt" style="font-size: 3rem;"></i>
                                    <h5 class="mt-2">Peta tidak dapat dimuat</h5>
                                    <p>Silakan input koordinat secara manual di atas</p>
                                </div>
                            </div>
                        </div>
                        
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Klik pada peta untuk memilih lokasi baru, atau gunakan tombol "Lokasi Saya" untuk menggunakan posisi saat ini
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', $geofencing->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Aktifkan Geofencing Ini</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Jika dicentang, geofencing lain akan dinonaktifkan secara otomatis
                        </small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.geofencing.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-info me-2" onclick="testCurrentLocation()">
                                <i class="bi bi-geo-alt me-1"></i>
                                Test Lokasi
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Update Geofencing
                            </button>
                        </div>
                    </div>
                </form>
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
                               value="{{ $geofencing->latitude }}" placeholder="Contoh: 3.5952" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control" id="testLng" 
                               value="{{ $geofencing->longitude }}" placeholder="Contoh: 98.6722" required>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-info w-100" onclick="getTestCurrentLocation()">
                            <i class="bi bi-geo-alt me-1"></i>
                            Gunakan Lokasi Saat Ini untuk Test
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
@endsection

@push('scripts')
<script>
    let map;
    let marker;
    let circle;
    let mapLoaded = false;
    
    // Original coordinates for reset
    const originalLat = {{ $geofencing->latitude }};
    const originalLng = {{ $geofencing->longitude }};

    // Initialize map without Google Maps API (fallback)
    function initMapFallback() {
        document.getElementById('map').style.display = 'none';
        document.getElementById('mapFallback').style.display = 'flex';
    }

    // Initialize map with coordinates
    function initMapWithCoordinates() {
        const currentLat = parseFloat(document.getElementById('latitude').value) || originalLat;
        const currentLng = parseFloat(document.getElementById('longitude').value) || originalLng;
        
        // Simple map simulation without Google Maps
        const mapDiv = document.getElementById('map');
        mapDiv.innerHTML = `
            <div style="height: 100%; background: linear-gradient(to bottom, #4a90e2, #7bb3f0); 
                        display: flex; align-items: center; justify-content: center; 
                        border-radius: 8px; position: relative;">
                <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="bi bi-geo-alt-fill text-danger" style="font-size: 2rem;"></i>
                    <h6 class="mt-2 mb-1">Lokasi Terpilih</h6>
                    <small class="text-muted">
                        Lat: <span id="mapLat">${currentLat}</span><br>
                        Lng: <span id="mapLng">${currentLng}</span><br>
                        Radius: <span id="mapRadius">{{ $geofencing->radius_meter }}</span>m
                    </small>
                    <div class="mt-2">
                        <span class="badge bg-info">
                            ${currentLat === originalLat && currentLng === originalLng ? 'Lokasi Asli' : 'Lokasi Diubah'}
                        </span>
                    </div>
                </div>
                <div style="position: absolute; bottom: 10px; left: 10px; right: 10px;">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="openGoogleMaps()" style="width: 100%;">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        Buka di Google Maps
                    </button>
                </div>
            </div>
        `;
        
        updateMapDisplay();
        mapLoaded = true;
    }

    function updateMapDisplay() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        const radius = document.querySelector('input[name="radius_meter"]').value;
        
        if (mapLoaded) {
            document.getElementById('mapLat').textContent = lat.toFixed(6);
            document.getElementById('mapLng').textContent = lng.toFixed(6);
            document.getElementById('mapRadius').textContent = radius;
            
            // Update badge
            const badge = document.querySelector('.badge');
            if (lat === originalLat && lng === originalLng) {
                badge.className = 'badge bg-info';
                badge.textContent = 'Lokasi Asli';
            } else {
                badge.className = 'badge bg-warning';
                badge.textContent = 'Lokasi Diubah';
            }
        }
    }

    function openGoogleMaps() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
    }

    function resetToOriginal() {
        document.getElementById('latitude').value = originalLat;
        document.getElementById('longitude').value = originalLng;
        updateMapDisplay();
        
        // Show success message
        showAlert('success', 'Koordinat berhasil direset ke lokasi asli');
    }

    function getCurrentLocation() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Getting...';
        btn.disabled = true;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                updateMapDisplay();
                
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                showAlert('success', `Lokasi berhasil diambil: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
                
            }, function(error) {
                let errorMsg = 'Gagal mendapatkan lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Akses lokasi ditolak. Mohon berikan izin akses lokasi.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Request timeout. Silakan coba lagi.';
                        break;
                    default:
                        errorMsg += error.message;
                        break;
                }
                alert(errorMsg);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            });
        } else {
            alert('Geolocation tidak didukung oleh browser ini.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    function testCurrentLocation() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        
        document.getElementById('testLat').value = lat;
        document.getElementById('testLng').value = lng;
        document.getElementById('testResult').style.display = 'none';
        
        new bootstrap.Modal(document.getElementById('testLocationModal')).show();
    }

    function getTestCurrentLocation() {
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Getting location...';
        btn.disabled = true;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('testLat').value = position.coords.latitude;
                document.getElementById('testLng').value = position.coords.longitude;
                btn.innerHTML = originalHtml;
                btn.disabled = false;
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
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            });
        } else {
            alert('Geolocation tidak didukung oleh browser ini.');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    function submitTestLocation() {
        const lat = document.getElementById('testLat').value;
        const lng = document.getElementById('testLng').value;
        const resultDiv = document.getElementById('testResult');

        if (!lat || !lng) {
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
                geofencing_id: {{ $geofencing->id }}
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

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-1"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.coordinate-info');
        container.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        initMapWithCoordinates();
        
        // Update map when coordinates change
        document.getElementById('latitude').addEventListener('input', updateMapDisplay);
        document.getElementById('longitude').addEventListener('input', updateMapDisplay);
        document.querySelector('input[name="radius_meter"]').addEventListener('input', updateMapDisplay);
    });
</script>
@endpush