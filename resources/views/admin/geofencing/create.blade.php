@extends('layouts.admin')

@section('title', 'Tambah Geofencing - ADMA Absensi Kantor')
@section('page-title', 'Tambah Pengaturan Geofencing')

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
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    Form Tambah Pengaturan Geofencing
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tips:</strong> Koordinat default adalah lokasi Medan. Anda bisa mengklik peta, 
                    menggunakan lokasi saat ini, atau input manual koordinat.
                </div>

                <form action="{{ route('admin.geofencing.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lokasi" class="form-control @error('nama_lokasi') is-invalid @enderror" 
                                       value="{{ old('nama_lokasi', 'Kantor ADMA Medan') }}" 
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
                                       value="{{ old('radius_meter', 100) }}" min="10" max="1000" required>
                                @error('radius_meter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Radius area dalam meter (10-1000m). Semakin kecil semakin ketat.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="latitude" id="latitude" 
                                       class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude', 3.5952) }}" required>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Contoh: 3.5952 (Medan)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="longitude" id="longitude" 
                                       class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude', 98.6722) }}" required>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Contoh: 98.6722 (Medan)</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                  rows="3" placeholder="Deskripsi lokasi geofencing (opsional)">{{ old('deskripsi', 'Area geofencing untuk absensi karyawan di lokasi kantor') }}</textarea>
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
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="getCurrentLocation()">
                                <i class="bi bi-geo-alt me-1"></i>
                                Lokasi Saya
                            </button>
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
                            Klik pada peta untuk memilih lokasi, atau gunakan tombol "Lokasi Saya" untuk menggunakan posisi saat ini
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Simpan Geofencing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Note: Replace YOUR_GOOGLE_MAPS_API_KEY with actual API key -->
<script>
    let map;
    let marker;
    let circle;
    let mapLoaded = false;

    // Initialize map without Google Maps API (fallback)
    function initMapFallback() {
        document.getElementById('map').style.display = 'none';
        document.getElementById('mapFallback').style.display = 'flex';
    }

    // Initialize map with coordinates
    function initMapWithCoordinates() {
        const defaultLat = parseFloat(document.getElementById('latitude').value) || 3.5952;
        const defaultLng = parseFloat(document.getElementById('longitude').value) || 98.6722;
        
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
                        Lat: <span id="mapLat">${defaultLat}</span><br>
                        Lng: <span id="mapLng">${defaultLng}</span><br>
                        Radius: <span id="mapRadius">100</span>m
                    </small>
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
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        const radius = document.querySelector('input[name="radius_meter"]').value;
        
        if (mapLoaded) {
            document.getElementById('mapLat').textContent = lat;
            document.getElementById('mapLng').textContent = lng;
            document.getElementById('mapRadius').textContent = radius;
        }
    }

    function openGoogleMaps() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
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
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show mt-2';
                alert.innerHTML = `
                    <i class="bi bi-check-circle me-1"></i>
                    Lokasi berhasil diambil: ${lat.toFixed(6)}, ${lng.toFixed(6)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                btn.parentNode.appendChild(alert);
                
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 3000);
                
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