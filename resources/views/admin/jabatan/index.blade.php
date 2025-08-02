@extends('layouts.admin')

@section('title', 'Manajemen Jabatan - SIABSEN PMB')
@section('page-title', 'Manajemen Jabatan')

@push('styles')
<style>
    .jabatan-card {
        transition: transform 0.2s ease;
        border-left: 4px solid #667eea;
    }
    .jabatan-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .jadwal-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        margin-top: 8px;
    }
    .jadwal-badge {
        font-size: 0.75rem;
        padding: 2px 6px;
    }
    .stats-mini {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-briefcase me-2"></i>
                    Data Jabatan
                </h5>
                <a href="{{ route('admin.jabatan.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Jabatan
                </a>
            </div>
            <div class="card-body">
                <!-- Search Filter -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Cari nama jabatan atau deskripsi..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    @if(request('search'))
                        <div class="mt-2">
                            <a href="{{ route('admin.jabatan.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x"></i> Clear Filter
                            </a>
                        </div>
                    @endif
                </form>

                <!-- Jabatan Cards Grid -->
                <div class="row">
                    @forelse($jabatans as $jabatan)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card jabatan-card h-100">
                                <div class="card-header bg-transparent border-0 pb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-0 fw-bold text-primary">
                                            {{ $jabatan->nama_jabatan }}
                                        </h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.jabatan.show', $jabatan) }}">
                                                        <i class="bi bi-eye me-2"></i>Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.jabatan.edit', $jabatan) }}">
                                                        <i class="bi bi-pencil me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item text-danger" 
                                                            onclick="deleteJabatan({{ $jabatan->id }})">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-2">
                                    <!-- Description -->
                                    <p class="text-muted small mb-3">
                                        {{ $jabatan->deskripsi ?: 'Tidak ada deskripsi' }}
                                    </p>

                                    <!-- Working Schedule -->
                                    <div class="mb-3">
                                        <small class="fw-bold text-secondary">Jadwal Kerja:</small>
                                        <div class="jadwal-badges">
                                            @php
                                                $jadwal = $jabatan->jadwal_kerja ?? [];
                                                $days = [
                                                    'senin' => 'Sen',
                                                    'selasa' => 'Sel',
                                                    'rabu' => 'Rab',
                                                    'kamis' => 'Kam',
                                                    'jumat' => 'Jum',
                                                    'sabtu' => 'Sab',
                                                    'minggu' => 'Min'
                                                ];
                                            @endphp
                                            @foreach($days as $key => $label)
                                                <span class="badge jadwal-badge {{ ($jadwal[$key] ?? false) ? 'bg-success' : 'bg-light text-dark' }}">
                                                    {{ $label }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if($jabatan->keterangan_jadwal)
                                            <small class="text-muted d-block mt-1">
                                                {{ $jabatan->keterangan_jadwal }}
                                            </small>
                                        @endif
                                    </div>

                                    <!-- Statistics Mini -->
                                    <div class="stats-mini">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="fw-bold text-primary">{{ $jabatan->total_active_users }}</div>
                                                <small class="text-muted">User Aktif</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="fw-bold text-warning">{{ $jabatan->toleransi_terlambat }}m</div>
                                                <small class="text-muted">Toleransi</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-plus me-1"></i>
                                            {{ $jabatan->created_at->diffForHumans() }}
                                        </small>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.jabatan.show', $jabatan) }}" 
                                               class="btn btn-outline-info" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.jabatan.edit', $jabatan) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-briefcase" style="font-size: 3rem; opacity: 0.3;"></i>
                                <h5 class="mt-3">
                                    @if(request('search'))
                                        Tidak ada jabatan yang sesuai dengan pencarian "{{ request('search') }}"
                                    @else
                                        Belum ada jabatan yang ditambahkan
                                    @endif
                                </h5>
                                <p>
                                    @if(request('search'))
                                        Coba kata kunci yang berbeda atau
                                        <a href="{{ route('admin.jabatan.index') }}">lihat semua jabatan</a>
                                    @else
                                        Tambahkan jabatan pertama untuk mulai mengelola user
                                    @endif
                                </p>
                                @if(!request('search'))
                                    <a href="{{ route('admin.jabatan.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Tambah Jabatan Pertama
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            Menampilkan {{ $jabatans->firstItem() ?? 0 }} - {{ $jabatans->lastItem() ?? 0 }} 
                            dari {{ $jabatans->total() }} jabatan
                        </small>
                    </div>
                    <div>
                        {{ $jabatans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Konfirmasi Hapus Jabatan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Perhatian:</strong> Jabatan yang memiliki user tidak dapat dihapus. 
                    Pindahkan semua user ke jabatan lain terlebih dahulu.
                </div>
                <p>Apakah Anda yakin ingin menghapus jabatan <strong id="jabatan-name"></strong>?</p>
                <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bi bi-trash me-1"></i>
                    Hapus Jabatan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let deleteJabatanId = null;

    function deleteJabatan(jabatanId) {
        deleteJabatanId = jabatanId;
        
        // Get jabatan name from the card
        const jabatanCard = document.querySelector(`[onclick="deleteJabatan(${jabatanId})"]`)
            .closest('.jabatan-card');
        const jabatanName = jabatanCard.querySelector('.fw-bold.text-primary').textContent.trim();
        
        document.getElementById('jabatan-name').textContent = jabatanName;
        
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!deleteJabatanId) return;
        
        const button = this;
        const originalText = button.innerHTML;
        
        // Show loading
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';
        button.disabled = true;
        
        // Make AJAX request
        fetch(`/admin/jabatan/${deleteJabatanId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                
                // Show success message
                showAlert('success', data.message);
                
                // Reload page after delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Show error message
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat menghapus jabatan. Silakan coba lagi.');
        })
        .finally(() => {
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of main content
        const mainContent = document.querySelector('main .row');
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush