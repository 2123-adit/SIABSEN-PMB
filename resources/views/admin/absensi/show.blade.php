@extends('layouts.admin')

@section('title', 'Detail Absensi - SIABSEN PMB')
@section('page-title', 'Detail Absensi')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi User</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nama:</strong></td>
                        <td>{{ $absensi->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td>{{ $absensi->user->username }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jabatan:</strong></td>
                        <td>{{ $absensi->user->jabatan->nama_jabatan }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal:</strong></td>
                        <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Absensi</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Status Kehadiran:</strong></td>
                        <td>
                            <span class="badge bg-{{ $absensi->status_badge }}">
                                {{ ucfirst($absensi->status_kehadiran) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jam Masuk:</strong></td>
                        <td>
                            {{ $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i:s') : '-' }}
                            @if($absensi->status_masuk == 'terlambat')
                                <span class="badge bg-warning">Terlambat {{ $absensi->menit_terlambat }} menit</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jam Pulang:</strong></td>
                        <td>{{ $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i:s') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Jam Kerja:</strong></td>
                        <td>{{ $absensi->total_jam_kerja }} jam</td>
                    </tr>
                    
                    <!-- Geofencing Info -->
                    @if($absensi->latitude_masuk || $absensi->latitude_pulang)
                    <tr>
                        <td><strong>Status Geofencing:</strong></td>
                        <td>
                            @if($absensi->latitude_masuk)
                                <span class="badge bg-{{ $absensi->is_within_geofence_masuk ? 'success' : 'danger' }}">
                                    Masuk: {{ $absensi->is_within_geofence_masuk ? 'Dalam Area' : 'Luar Area' }}
                                </span>
                                @if($absensi->distance_from_office_masuk)
                                    <small class="text-muted">({{ $absensi->distance_from_office_masuk }}m)</small>
                                @endif
                                <br>
                            @endif
                            @if($absensi->latitude_pulang)
                                <span class="badge bg-{{ $absensi->is_within_geofence_pulang ? 'success' : 'danger' }}">
                                    Pulang: {{ $absensi->is_within_geofence_pulang ? 'Dalam Area' : 'Luar Area' }}
                                </span>
                                @if($absensi->distance_from_office_pulang)
                                    <small class="text-muted">({{ $absensi->distance_from_office_pulang }}m)</small>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@if($absensi->foto_masuk || $absensi->foto_pulang)
<div class="row mt-3">
    @if($absensi->foto_masuk)
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Foto Absen Masuk</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $absensi->foto_masuk) }}" 
                     alt="Foto Absen Masuk" class="img-fluid rounded" style="max-height: 300px;">
                @if($absensi->google_maps_link_masuk)
                    <div class="mt-2">
                        <a href="{{ $absensi->google_maps_link_masuk }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-geo-alt"></i> Lihat Lokasi
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    @if($absensi->foto_pulang)
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Foto Absen Pulang</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $absensi->foto_pulang) }}" 
                     alt="Foto Absen Pulang" class="img-fluid rounded" style="max-height: 300px;">
                @if($absensi->google_maps_link_pulang)
                    <div class="mt-2">
                        <a href="{{ $absensi->google_maps_link_pulang }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-geo-alt"></i> Lihat Lokasi
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<div class="mt-3">
    <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection