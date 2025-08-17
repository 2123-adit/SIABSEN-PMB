@extends('layouts.admin')

@section('title', 'Detail User - SIABSEN PMB')
@section('page-title', 'Detail User')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profil User</h5>
            </div>
            <div class="card-body text-center">
                @if($user->foto_profil)
                    <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="Foto {{ $user->name }}" 
                         class="rounded-circle mb-3" width="150" height="150">
                @else
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 150px; height: 150px;">
                        <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                    </div>
                @endif

                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->jabatan->nama_jabatan }}</p>
                <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }} fs-6">
                    {{ ucfirst($user->status) }}
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Detail</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>User ID:</strong></td>
                                <td>{{ $user->user_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jabatan:</strong></td>
                                <td>{{ $user->jabatan->nama_jabatan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jadwal Kerja:</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $user->jabatan->jadwal_display }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Jam Masuk:</strong></td>
                                <td>{{ $user->jam_masuk->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jam Pulang:</strong></td>
                                <td>{{ $user->jam_pulang->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Terdaftar:</strong></td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Persentase Kehadiran:</strong></td>
                                <td>{{ $user->persentase_kehadiran }}%</td>
                            </tr>
                            <tr>
                                <td><strong>Total Terlambat:</strong></td>
                                <td>{{ $user->total_terlambat }} kali</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Kerja Detail -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Jadwal Kerja Detail</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $dayNames = [
                            'senin' => 'Senin',
                            'selasa' => 'Selasa', 
                            'rabu' => 'Rabu',
                            'kamis' => 'Kamis',
                            'jumat' => 'Jumat',
                            'sabtu' => 'Sabtu',
                            'minggu' => 'Minggu'
                        ];
                        $jadwalKerja = $user->jabatan->jadwal_kerja ?? [
                            'senin' => true, 'selasa' => true, 'rabu' => true, 
                            'kamis' => true, 'jumat' => true, 'sabtu' => false, 'minggu' => false
                        ];
                    @endphp
                    
                    @foreach($dayNames as $key => $dayName)
                        <div class="col-md-12 col-lg-6 col-xl-3 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($jadwalKerja[$key] ?? false)
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                    @endif
                                </div>
                                <div>
                                    <strong>{{ $dayName }}</strong><br>
                                    <small class="text-muted">
                                        @if($jadwalKerja[$key] ?? false)
                                            {{ $user->jam_masuk->format('H:i') }} - {{ $user->jam_pulang->format('H:i') }}
                                        @else
                                            Libur
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($user->jabatan->keterangan_jadwal)
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="text-muted">Keterangan Jadwal:</h6>
                        <p class="mb-0">{{ $user->jabatan->keterangan_jadwal }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistik Kehadiran -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Statistik Kehadiran</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="bg-success text-white rounded p-3">
                            <h4>{{ $statistik['total_hadir'] }}</h4>
                            <small>Hadir</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-warning text-white rounded p-3">
                            <h4>{{ $statistik['total_izin'] }}</h4>
                            <small>Izin</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-info text-white rounded p-3">
                            <h4>{{ $statistik['total_sakit'] }}</h4>
                            <small>Sakit</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-danger text-white rounded p-3">
                            <h4>{{ $statistik['total_alfa'] }}</h4>
                            <small>Alfa</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-dark text-white rounded p-3">
                            <h4>{{ $statistik['total_terlambat'] }}</h4>
                            <small>Terlambat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>
</div>
@endsection