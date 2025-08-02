@extends('layouts.admin')

@section('title', 'Laporan - SIABSEN PMB')
@section('page-title', 'Laporan Absensi')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Generate Laporan
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.laporan.generate') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="tanggal_mulai" class="form-control" required>
                                <small class="text-muted">Tanggal Mulai</small>
                            </div>
                            <div class="col-6">
                                <input type="date" name="tanggal_selesai" class="form-control" required>
                                <small class="text-muted">Tanggal Selesai</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">User (Opsional)</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jabatan (Opsional)</label>
                        <select name="jabatan_id" class="form-select">
                            <option value="">Semua Jabatan</option>
                            @foreach($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Kehadiran (Opsional)</label>
                        <select name="status_kehadiran" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alfa">Alfa</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Format <span class="text-danger">*</span></label>
                        <select name="format" class="form-select" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-download me-1"></i>
                        Generate Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-receipt me-2"></i>
                    Slip Absensi Individual
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.laporan.slip') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->jabatan->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-6">
                                <select name="bulan" class="form-select" required>
                                    <option value="">Bulan</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <select name="tahun" class="form-select" required>
                                    <option value="">Tahun</option>
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-download me-1"></i>
                        Download Slip
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Statistik
                </h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.laporan.statistik') }}" class="btn btn-outline-info w-100">
                    <i class="bi bi-graph-up me-1"></i>
                    Lihat Statistik Detail
                </a>
            </div>
        </div>
    </div>
</div>
@endsection