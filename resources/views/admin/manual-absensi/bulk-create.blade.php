@extends('layouts.admin')

@section('title', 'Bulk Absensi Manual - SIABSEN PMB')
@section('page-title', 'Bulk Absensi Manual')

@push('styles')
<style>
    .user-checkbox {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    .user-checkbox:hover {
        background-color: #f8f9fa;
        border-color: #667eea;
    }
    .user-checkbox.selected {
        background-color: #e3f2fd;
        border-color: #667eea;
    }
    .jabatan-group {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .jabatan-header {
        background: #667eea;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        margin: -15px -15px 15px -15px;
        font-weight: bold;
    }
    .select-all-jabatan {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        float: right;
        margin-top: -2px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Form Bulk Absensi Manual
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Fitur ini akan menambahkan absensi untuk beberapa karyawan sekaligus pada tanggal yang sama.
                    Pastikan tanggal dan status yang dipilih sudah benar.
                </div>

                <form action="{{ route('admin.manual-absensi.bulk-store') }}" method="POST" id="bulkForm">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                       value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status Kehadiran <span class="text-danger">*</span></label>
                                <select name="status_kehadiran" class="form-select @error('status_kehadiran') is-invalid @enderror" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="hadir" {{ old('status_kehadiran') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="izin" {{ old('status_kehadiran') == 'izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="sakit" {{ old('status_kehadiran') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="alfa" {{ old('status_kehadiran') == 'alfa' ? 'selected' : '' }}>Alfa</option>
                                </select>
                                @error('status_kehadiran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Filter Jabatan</label>
                                <select id="filter_jabatan" class="form-select">
                                    <option value="">Semua Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Filter untuk memudahkan pencarian karyawan</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                  rows="2" placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Pilih Karyawan <span class="text-danger">*</span>
                            </h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-success" onclick="selectAll(true)">
                                    <i class="bi bi-check-all"></i> Pilih Semua
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">
                                    <i class="bi bi-x"></i> Batal Semua
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            @if($errors->has('users'))
                                <div class="alert alert-danger">{{ $errors->first('users') }}</div>
                            @endif
                            
                            <div id="users-container">
                                @foreach($jabatans as $jabatan)
                                    @php
                                        $jabatanUsers = $users->where('jabatan_id', $jabatan->id);
                                    @endphp
                                    
                                    @if($jabatanUsers->count() > 0)
                                        <div class="jabatan-group" data-jabatan="{{ $jabatan->id }}">
                                            <div class="jabatan-header">
                                                {{ $jabatan->nama_jabatan }} ({{ $jabatanUsers->count() }} orang)
                                                <button type="button" class="select-all-jabatan" onclick="selectJabatan({{ $jabatan->id }}, true)">
                                                    Pilih Semua
                                                </button>
                                            </div>
                                            
                                            <div class="row">
                                                @foreach($jabatanUsers as $user)
                                                    <div class="col-md-6 col-lg-4 user-item" data-jabatan="{{ $jabatan->id }}">
                                                        <div class="user-checkbox">
                                                            <div class="form-check">
                                                                <input class="form-check-input user-checkbox-input" type="checkbox" 
                                                                       name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}"
                                                                       {{ in_array($user->id, old('users', [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label w-100" for="user_{{ $user->id }}">
                                                                    <div class="fw-bold">{{ $user->name }}</div>
                                                                    <small class="text-muted">
                                                                        {{ $user->username }}<br>
                                                                        {{ $user->jam_masuk->format('H:i') }} - {{ $user->jam_pulang->format('H:i') }}
                                                                    </small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Total dipilih: <strong><span id="selected-count">0</span></strong> karyawan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.manual-absensi.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Simpan Bulk Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterJabatan = document.getElementById('filter_jabatan');
        const usersContainer = document.getElementById('users-container');
        const selectedCount = document.getElementById('selected-count');
        
        // Update selected count
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.user-checkbox-input:checked').length;
            selectedCount.textContent = checked;
        }
        
        // Filter by jabatan
        filterJabatan.addEventListener('change', function() {
            const selectedJabatan = this.value;
            const jabatanGroups = document.querySelectorAll('.jabatan-group');
            
            jabatanGroups.forEach(function(group) {
                if (selectedJabatan === '' || group.dataset.jabatan === selectedJabatan) {
                    group.style.display = 'block';
                } else {
                    group.style.display = 'none';
                }
            });
        });
        
        // Listen for checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('user-checkbox-input')) {
                updateSelectedCount();
                
                // Toggle visual state
                const userBox = e.target.closest('.user-checkbox');
                if (e.target.checked) {
                    userBox.classList.add('selected');
                } else {
                    userBox.classList.remove('selected');
                }
            }
        });
        
        // Initial count
        updateSelectedCount();
    });
    
    function selectAll(checked) {
        const checkboxes = document.querySelectorAll('.user-checkbox-input');
        const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
            const group = cb.closest('.jabatan-group');
            return group && group.style.display !== 'none';
        });
        
        visibleCheckboxes.forEach(function(checkbox) {
            checkbox.checked = checked;
            const userBox = checkbox.closest('.user-checkbox');
            if (checked) {
                userBox.classList.add('selected');
            } else {
                userBox.classList.remove('selected');
            }
        });
        
        updateSelectedCount();
    }
    
    function selectJabatan(jabatanId, checked) {
        const checkboxes = document.querySelectorAll(`.user-item[data-jabatan="${jabatanId}"] .user-checkbox-input`);
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = checked;
            const userBox = checkbox.closest('.user-checkbox');
            if (checked) {
                userBox.classList.add('selected');
            } else {
                userBox.classList.remove('selected');
            }
        });
        
        updateSelectedCount();
    }
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.user-checkbox-input:checked').length;
        document.getElementById('selected-count').textContent = checked;
    }
    
    // Form validation
    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        const selectedUsers = document.querySelectorAll('.user-checkbox-input:checked').length;
        if (selectedUsers === 0) {
            e.preventDefault();
            alert('Mohon pilih minimal 1 karyawan untuk bulk absensi');
            return false;
        }
        
        if (!confirm(`Yakin ingin menambahkan absensi untuk ${selectedUsers} karyawan?`)) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush