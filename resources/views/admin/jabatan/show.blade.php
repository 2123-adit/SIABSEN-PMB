@extends('layouts.admin')

@section('title', 'Detail Jabatan - ADMA Absensi Kantor')
@section('page-title', 'Detail Jabatan')

@push('styles')
<style>
    .jabatan-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .info-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #667eea;
    }
    .info-label {
        font-weight: bold;
        color: #495057;
        display: block;
        margin-bottom: 5px;
    }
    .info-value {
        color: #212529;
        font-size: 1.1em;
    }
    .jadwal-display {
        background: #e8f5e8;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #28a745;
    }
    .day-badge {
        display: inline-block;
        background: #667eea;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        margin: 2px;
        font-size: 0.85em;
        font-weight: 500;
    }
    .day-badge.inactive {
        background: #6c757d;
        opacity: 0.6;
    }
    .tolerance-card {
        background: #fff3cd;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }
    .users-list-card {
        background: #d1ecf1;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }
    .user-item {
        background: white;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 8px;
        border: 1px solid #dee2e6;
    }
    .status-badge {
        font-size: 0.75em;
        padding: 2px 8px;
        border-radius: 10px;
    }
    .status-aktif {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-nonaktif {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .quick-stats {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        display: block;
    }
    .stat-label {
        font-size: 0.9em;
        opacity: 0.9;
    }
    .action-buttons {
        position: sticky;
        top: 20px;
        z-index: 100;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Jabatan Info Header -->
        <div class="jabatan-info-card">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-briefcase-fill" style="font-size: 2.5rem;"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $jabatan->nama_jabatan }}</h4>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-calendar-plus me-1"></i>Dibuat: {{ $jabatan->created_at->format('d M Y, H:i') }} • 
                                <i class="bi bi-arrow-clockwise me-1"></i>Update: {{ $jabatan->updated_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quick-stats">
                        <span class="stat-number">{{ $jabatan->users->where('status', 'aktif')->count() }}</span>
                        <span class="stat-label">User Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Informasi Jabatan
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-briefcase me-1"></i>
                                Nama Jabatan
                            </span>
                            <span class="info-value">{{ $jabatan->nama_jabatan }}</span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-file-text me-1"></i>
                                Deskripsi
                            </span>
                            <span class="info-value">
                                {{ $jabatan->deskripsi ?: 'Tidak ada deskripsi' }}
                            </span>
                        </div>

                        <!-- Tolerance Information -->
                        <div class="tolerance-card">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-fill me-2" style="font-size: 1.5rem; color: #ffc107;"></i>
                                <div>
                                    <span class="info-label">Toleransi Terlambat</span>
                                    <span class="info-value">{{ $jabatan->toleransi_terlambat }} Menit</span>
                                    <small class="d-block text-muted">
                                        Karyawan dianggap terlambat jika absen lebih dari {{ $jabatan->toleransi_terlambat }} menit dari jam masuk
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Information -->
                        <div class="jadwal-display">
                            <h6 class="mb-3">
                                <i class="bi bi-calendar-week me-2"></i>
                                Jadwal Kerja
                            </h6>
                            
                            @php
                                $days = [
                                    'senin' => 'Senin',
                                    'selasa' => 'Selasa', 
                                    'rabu' => 'Rabu',
                                    'kamis' => 'Kamis',
                                    'jumat' => 'Jumat',
                                    'sabtu' => 'Sabtu',
                                    'minggu' => 'Minggu'
                                ];
                                $jadwalKerja = $jabatan->jadwal_kerja ?? [];
                            @endphp
                            
                            <div class="mb-3">
                                @foreach($days as $key => $day)
                                    <span class="day-badge {{ ($jadwalKerja[$key] ?? false) ? '' : 'inactive' }}">
                                        {{ $day }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="info-item mb-2">
                                <span class="info-label">Pola Jadwal</span>
                                <span class="info-value">{{ $jabatan->jadwal_display ?? 'Custom Schedule' }}</span>
                            </div>

                            @if($jabatan->keterangan_jadwal)
                                <div class="info-item">
                                    <span class="info-label">Keterangan Jadwal</span>
                                    <span class="info-value">{{ $jabatan->keterangan_jadwal }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Users List -->
                @if($jabatan->users->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Daftar User ({{ $jabatan->users->count() }} orang)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="users-list-card">
                                @foreach($jabatan->users as $user)
                                    <div class="user-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>
                                                    <i class="bi bi-person me-1"></i>
                                                    {{ $user->name }}
                                                </strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="status-badge status-{{ $user->status }}">
                                                    {{ ucfirst($user->status) }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $user->updated_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Buttons Sidebar -->
            <div class="col-md-4">
                <div class="action-buttons">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-gear me-1"></i>
                                Aksi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.jabatan.edit', $jabatan) }}" 
                                   class="btn btn-warning">
                                    <i class="bi bi-pencil-square me-1"></i>
                                    Edit Jabatan
                                </a>
                                
                                <a href="{{ route('admin.jabatan.index') }}" 
                                   class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Kembali ke Daftar
                                </a>
                                
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick="printDetail()">
                                    <i class="bi bi-printer me-1"></i>
                                    Cetak Detail
                                </button>
                                
                                @if($jabatan->users->where('status', 'aktif')->count() == 0)
                                    <button type="button" class="btn btn-danger" 
                                            onclick="confirmDelete()">
                                        <i class="bi bi-trash me-1"></i>
                                        Hapus Jabatan
                                    </button>
                                @else
                                    <div class="alert alert-warning mb-0 p-2">
                                        <small>
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Jabatan tidak dapat dihapus karena masih digunakan oleh 
                                            {{ $jabatan->users->where('status', 'aktif')->count() }} user aktif
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-1"></i>
                                Statistik
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="mb-2">
                                        <span class="d-block fw-bold text-success" style="font-size: 1.5rem;">
                                            {{ $jabatan->users->where('status', 'aktif')->count() }}
                                        </span>
                                        <small class="text-muted">User Aktif</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-2">
                                        <span class="d-block fw-bold text-secondary" style="font-size: 1.5rem;">
                                            {{ $jabatan->users->where('status', 'nonaktif')->count() }}
                                        </span>
                                        <small class="text-muted">User Nonaktif</small>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="text-center">
                                <div class="mb-2">
                                    <span class="d-block fw-bold text-primary" style="font-size: 1.2rem;">
                                        {{ collect($jadwalKerja)->filter()->count() }}
                                    </span>
                                    <small class="text-muted">Hari Kerja per Minggu</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Log (if available) -->
                    @if(method_exists($jabatan, 'activities') && $jabatan->activities->count() > 0)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Aktivitas Terakhir
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($jabatan->activities->take(3) as $activity)
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2">
                                            <i class="bi bi-dot" style="font-size: 1.5rem; color: #667eea;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                {{ $activity->description }}
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if($jabatan->users->where('status', 'aktif')->count() == 0)
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus jabatan <strong>"{{ $jabatan->nama_jabatan }}"</strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>
                        Batal
                    </button>
                    <form action="{{ route('admin.jabatan.destroy', $jabatan) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    // Confirm delete function
    function confirmDelete() {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Print detail function
    function printDetail() {
        // Create print-friendly version
        const printContent = `
            <html>
                <head>
                    <title>Detail Jabatan - {{ $jabatan->nama_jabatan }}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .info-section { margin-bottom: 20px; }
                        .label { font-weight: bold; }
                        .day-badge { 
                            display: inline-block; 
                            background: #667eea; 
                            color: white; 
                            padding: 3px 8px; 
                            border-radius: 10px; 
                            margin: 2px; 
                            font-size: 0.8em; 
                        }
                        .day-badge.inactive { background: #ccc; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Detail Jabatan</h2>
                        <h3>{{ $jabatan->nama_jabatan }}</h3>
                        <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</p>
                    </div>
                    
                    <div class="info-section">
                        <h4>Informasi Umum</h4>
                        <p><span class="label">Nama Jabatan:</span> {{ $jabatan->nama_jabatan }}</p>
                        <p><span class="label">Deskripsi:</span> {{ $jabatan->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                        <p><span class="label">Toleransi Terlambat:</span> {{ $jabatan->toleransi_terlambat }} menit</p>
                        <p><span class="label">Dibuat:</span> {{ $jabatan->created_at->format('d M Y, H:i') }}</p>
                        <p><span class="label">Terakhir Update:</span> {{ $jabatan->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                    
                    <div class="info-section">
                        <h4>Jadwal Kerja</h4>
                        <p><span class="label">Pola:</span> {{ $jabatan->jadwal_display ?? 'Custom Schedule' }}</p>
                        <div>
                            @foreach($days as $key => $day)
                                <span class="day-badge {{ ($jadwalKerja[$key] ?? false) ? '' : 'inactive' }}">
                                    {{ $day }}
                                </span>
                            @endforeach
                        </div>
                        @if($jabatan->keterangan_jadwal)
                            <p><span class="label">Keterangan:</span> {{ $jabatan->keterangan_jadwal }}</p>
                        @endif
                    </div>
                    
                    @if($jabatan->users->count() > 0)
                        <div class="info-section">
                            <h4>Daftar User ({{ $jabatan->users->count() }} orang)</h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jabatan->users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ ucfirst($user->status) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </body>
            </html>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    // Auto refresh user count every 30 seconds (optional)
    function refreshUserCount() {
        // This would typically make an AJAX call to get updated user count
        // For now, we'll just log it
        console.log('Refreshing user count...');
    }

    // Initialize tooltips if Bootstrap is available
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    });

    // Handle responsive actions for mobile
    function handleMobileActions() {
        if (window.innerWidth < 768) {
            const actionButtons = document.querySelector('.action-buttons');
            if (actionButtons) {
                actionButtons.style.position = 'static';
            }
        }
    }

    // Listen for window resize
    window.addEventListener('resize', handleMobileActions);
    window.addEventListener('load', handleMobileActions);
</script>
@endpush