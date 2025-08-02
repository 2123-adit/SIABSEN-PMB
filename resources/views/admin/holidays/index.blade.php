@extends('layouts.admin')

@section('title', 'Hari Libur - SIABSEN PMB')
@section('page-title', 'Manajemen Hari Libur')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-x me-2"></i>
                    Data Hari Libur
                </h5>
                <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Hari Libur
                </a>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                                    <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="nasional" {{ request('jenis') == 'nasional' ? 'selected' : '' }}>Nasional</option>
                                <option value="cuti_bersama" {{ request('jenis') == 'cuti_bersama' ? 'selected' : '' }}>Cuti Bersama</option>
                                <option value="khusus" {{ request('jenis') == 'khusus' ? 'selected' : '' }}>Khusus</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Nama Libur</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $index => $holiday)
                                <tr>
                                    <td>{{ $holidays->firstItem() + $index }}</td>
                                    <td>{{ $holiday->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $holiday->nama_libur }}</td>
                                    <td>
                                        @if($holiday->jenis == 'nasional')
                                            <span class="badge bg-danger">Hari Libur Nasional</span>
                                        @elseif($holiday->jenis == 'cuti_bersama')
                                            <span class="badge bg-warning">Cuti Bersama</span>
                                        @else
                                            <span class="badge bg-info">Libur Khusus</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $holiday->is_active ? 'success' : 'secondary' }}">
                                            {{ $holiday->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.holidays.edit', $holiday) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-{{ $holiday->is_active ? 'secondary' : 'success' }}" 
                                                    onclick="toggleStatus({{ $holiday->id }})" 
                                                    title="{{ $holiday->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi bi-{{ $holiday->is_active ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteHoliday({{ $holiday->id }})" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="bi bi-inbox"></i> Tidak ada data hari libur
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $holidays->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(id) {
        if (confirm('Yakin ingin mengubah status hari libur ini?')) {
            fetch(`/admin/holidays/${id}/toggle`, {
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
                    alert('Gagal mengubah status.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        }
    }

    function deleteHoliday(id) {
        if (confirm('Yakin ingin menghapus hari libur ini?')) {
            fetch(`/admin/holidays/${id}`, {
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
                    location.reload();
                } else {
                    alert('Gagal menghapus hari libur.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        }
    }
</script>
@endpush
