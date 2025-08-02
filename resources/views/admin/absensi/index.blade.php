@extends('layouts.admin')

@section('title', 'Data Absensi - SIABSEN PMB')
@section('page-title', 'Data Absensi')

@push('styles')
<style>
    /* PHOTO PREVIEW STYLES */
    .photo-preview {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }
    .photo-preview:hover {
        transform: scale(1.1);
        border-color: #667eea;
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
    .photo-container {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    .no-photo {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 12px;
        border: 1px dashed #dee2e6;
    }
    
    /* MODAL PHOTO VIEWER */
    .photo-modal-content {
        max-width: 90vw;
        max-height: 90vh;
    }
    .photo-modal img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
    }
    .photo-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check me-2"></i>
                    Data Absensi
                </h5>
                <div class="btn-group">
                    <a href="{{ route('admin.absensi.calendar') }}" class="btn btn-outline-primary">
                        <i class="bi bi-calendar me-1"></i>
                        View Calendar
                    </a>
                    <a href="{{ route('admin.manual-absensi.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>
                        Tambah Manual
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter tetap sama -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">Semua User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jabatan</label>
                            <select name="jabatan_id" class="form-select">
                                <option value="">Semua Jabatan</option>
                                @foreach($jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}" {{ request('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                        {{ $jabatan->nama_jabatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status Kehadiran</label>
                            <select name="status_kehadiran" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status_kehadiran') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="izin" {{ request('status_kehadiran') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status_kehadiran') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alfa" {{ request('status_kehadiran') == 'alfa' ? 'selected' : '' }}>Alfa</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- UPDATED TABLE - DENGAN KOLOM FOTO -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Foto Absensi</th> <!-- KOLOM BARU -->
                                <th>Status</th>
                                <th>Type</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensis as $index => $absensi)
                                <tr>
                                    <td>{{ $absensis->firstItem() + $index }}</td>
                                    <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $absensi->user->name }}</td>
                                    <td>{{ $absensi->user->jabatan->nama_jabatan }}</td>
                                    <td>
                                        @if($absensi->jam_masuk)
                                            {{ $absensi->jam_masuk->format('H:i') }}
                                            @if($absensi->status_masuk == 'terlambat')
                                                <span class="badge bg-warning">{{ $absensi->menit_terlambat }}m</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i') : '-' }}
                                    </td>
                                    
                                    <!-- KOLOM FOTO ABSENSI - BARU -->
                                    <td>
                                        <div class="photo-container">
                                            @if($absensi->foto_masuk)
                                                <img src="{{ \App\Helpers\ImageHelper::secureImageUrl($absensi->foto_masuk) ?? asset('storage/' . $absensi->foto_masuk) }}" 
                                                     alt="Foto Masuk" 
                                                     class="photo-preview" 
                                                     onclick="showPhotoModal('{{ $absensi->foto_masuk }}', 'Foto Masuk', '{{ $absensi->user->name }}', '{{ $absensi->tanggal->format('d/m/Y H:i') }}')"
                                                     title="Foto Masuk - Click untuk lihat besar">
                                            @else
                                                <div class="no-photo" title="Tidak ada foto masuk">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            @endif
                                            
                                            @if($absensi->foto_pulang)
                                                <img src="{{ \App\Helpers\ImageHelper::secureImageUrl($absensi->foto_pulang) ?? asset('storage/' . $absensi->foto_pulang) }}" 
                                                     alt="Foto Pulang" 
                                                     class="photo-preview" 
                                                     onclick="showPhotoModal('{{ $absensi->foto_pulang }}', 'Foto Pulang', '{{ $absensi->user->name }}', '{{ $absensi->tanggal->format('d/m/Y H:i') }}')"
                                                     title="Foto Pulang - Click untuk lihat besar">
                                            @else
                                                <div class="no-photo" title="Tidak ada foto pulang">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <span class="badge bg-{{ $absensi->status_badge }}">
                                            {{ ucfirst($absensi->status_kehadiran) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($absensi->foto_masuk && $absensi->foto_pulang)
                                            <span class="badge bg-primary">📱 Mobile</span>
                                        @elseif($absensi->jam_masuk || $absensi->jam_pulang)
                                            <span class="badge bg-info">✏️ Manual</span>
                                        @else
                                            <span class="badge bg-success">📝 Input</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.absensi.show', $absensi) }}" 
                                               class="btn btn-outline-info" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.absensi.edit', $absensi) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteAbsensi({{ $absensi->id }})" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <i class="bi bi-inbox"></i> Tidak ada data absensi
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $absensis->links() }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL UNTUK PREVIEW FOTO -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content photo-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">Foto Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="photo-modal">
                    <img id="photoModalImage" src="" alt="Foto Absensi" class="img-fluid rounded">
                </div>
                <div class="photo-info" id="photoModalInfo">
                    <!-- Info akan diisi via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="downloadPhoto()">
                    <i class="bi bi-download"></i> Download
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPhotoUrl = '';

    // SHOW PHOTO MODAL
    function showPhotoModal(photoPath, type, userName, datetime) {
        // Construct secure URL
        const photoUrl = photoPath.startsWith('http') ? photoPath : `/storage/${photoPath}`;
        currentPhotoUrl = photoUrl;
        
        // Set modal content
        document.getElementById('photoModalTitle').textContent = `${type} - ${userName}`;
        document.getElementById('photoModalImage').src = photoUrl;
        document.getElementById('photoModalInfo').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Karyawan:</strong><br>
                    <span class="text-muted">${userName}</span>
                </div>
                <div class="col-md-6">
                    <strong>Waktu:</strong><br>
                    <span class="text-muted">${datetime}</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Jenis:</strong><br>
                    <span class="badge bg-${type.includes('Masuk') ? 'success' : 'primary'}">${type}</span>
                </div>
                <div class="col-md-6">
                    <strong>File:</strong><br>
                    <span class="text-muted font-monospace">${photoPath.split('/').pop()}</span>
                </div>
            </div>
        `;
        
        // Show modal
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }

    // DOWNLOAD PHOTO
    function downloadPhoto() {
        if (!currentPhotoUrl) return;
        
        // Create download link
        const link = document.createElement('a');
        link.href = currentPhotoUrl;
        link.download = currentPhotoUrl.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // DELETE ABSENSI FUNCTION (existing)
    function deleteAbsensi(absensiId) {
        if (confirm('Apakah Anda yakin ingin menghapus data absensi ini?')) {
            // Show loading
            const loadingToast = document.createElement('div');
            loadingToast.className = 'toast-container position-fixed top-0 end-0 p-3';
            loadingToast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-body">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Menghapus data...
                    </div>
                </div>
            `;
            document.body.appendChild(loadingToast);
            
            fetch(`/admin/absensi/${absensiId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.body.removeChild(loadingToast);
                
                if (data.success) {
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show';
                    successAlert.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    const mainContent = document.querySelector('main .row');
                    mainContent.insertBefore(successAlert, mainContent.firstChild);
                    
                    setTimeout(() => {
                        if (successAlert.parentNode) {
                            successAlert.remove();
                        }
                    }, 3000);
                    
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Gagal menghapus data: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                if (loadingToast.parentNode) {
                    document.body.removeChild(loadingToast);
                }
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
            });
        }
    }

    // LAZY LOADING FOR IMAGES
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.photo-preview');
        
        // Add loading placeholder
        images.forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjRjhGOUZBIiBzdHJva2U9IiNERUUyRTYiIHN0cm9rZS1kYXNoYXJyYXk9IjIgMiIvPgo8cGF0aCBkPSJNMjAgMTJMMjggMjhIMTJMMjAgMTJaIiBmaWxsPSIjNkM3NTdEIiBmaWxsLW9wYWNpdHk9IjAuMyIvPgo8Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIyIiBmaWxsPSIjNkM3NTdEIiBmaWxsLW9wYWNpdHk9IjAuMyIvPgo8L3N2Zz4K';
                this.title = 'Foto tidak dapat dimuat';
                this.style.cursor = 'not-allowed';
            });
        });
    });
</script>
@endpush