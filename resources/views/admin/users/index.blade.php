@extends('layouts.admin')

@section('title', 'Manajemen User - SIABSEN PMB')
@section('page-title', 'Manajemen User')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Data User
                </h5>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah User
                </a>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
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
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Cari</label>
                            <input type="text" name="search" class="form-control" placeholder="Nama, NIK, atau Email" value="{{ request('search') }}">
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

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->jabatan->nama_jabatan }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}" 
                                               class="btn btn-outline-info" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="resetPassword({{ $user->id }})" title="Reset Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteUser({{ $user->id }})" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="bi bi-inbox"></i> Tidak ada data user
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $users->links() }}
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
                Apakah Anda yakin ingin menghapus user ini?
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

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Password akan direset menjadi <strong>password123</strong>. Lanjutkan?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="resetPasswordForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteUser(userId) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/users/' + userId;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function resetPassword(userId) {
        const form = document.getElementById('resetPasswordForm');
        form.action = '/admin/users/' + userId + '/reset-password';
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }
</script>
@endpush