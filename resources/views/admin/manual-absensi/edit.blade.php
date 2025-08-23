@extends('layouts.admin')

@section('title', 'Edit Absensi Manual - ADMA Absensi Kantor')
@section('page-title', 'Edit Absensi Manual')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Form Edit Absensi Manual
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Info:</strong> Edit data absensi untuk tanggal {{ $absensi->tanggal->format('d/m/Y') }}
                </div>

                <form action="{{ route('admin.manual-absensi.update', $absensi) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                data-jabatan="{{ $user->jabatan->nama_jabatan }}"
                                                data-jam-masuk="{{ $user->jam_masuk->format('H:i') }}"
                                                data-jam-pulang="{{ $user->jam_pulang->format('H:i') }}"
                                                {{ old('user_id', $absensi->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->user_id }}) - {{ $user->jabatan->nama_jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                       value="{{ old('tanggal', $absensi->tanggal->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status Kehadiran <span class="text-danger">*</span></label>
                                <select name="status_kehadiran" id="status_kehadiran" 
                                        class="form-select @error('status_kehadiran') is-invalid @enderror" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="hadir" {{ old('status_kehadiran', $absensi->status_kehadiran) == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="izin" {{ old('status_kehadiran', $absensi->status_kehadiran) == 'izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="sakit" {{ old('status_kehadiran', $absensi->status_kehadiran) == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="alfa" {{ old('status_kehadiran', $absensi->status_kehadiran) == 'alfa' ? 'selected' : '' }}>Alfa</option>
                                </select>
                                @error('status_kehadiran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4" id="jam_masuk_field">
                            <div class="mb-3">
                                <label class="form-label">Jam Masuk</label>
                                <input type="time" name="jam_masuk" id="jam_masuk" 
                                       class="form-control @error('jam_masuk') is-invalid @enderror" 
                                       value="{{ old('jam_masuk', $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i') : '') }}">
                                @error('jam_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Jam kerja default: <span id="default_jam_masuk">{{ $absensi->user->jam_masuk->format('H:i') }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-4" id="jam_pulang_field">
                            <div class="mb-3">
                                <label class="form-label">Jam Pulang</label>
                                <input type="time" name="jam_pulang" id="jam_pulang" 
                                       class="form-control @error('jam_pulang') is-invalid @enderror" 
                                       value="{{ old('jam_pulang', $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i') : '') }}">
                                @error('jam_pulang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Jam kerja default: <span id="default_jam_pulang">{{ $absensi->user->jam_pulang->format('H:i') }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                  rows="3" placeholder="Keterangan tambahan (opsional)">{{ old('keterangan', $absensi->keterangan) }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row" id="foto_fields">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Foto Masuk</label>
                                @if($absensi->foto_masuk)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $absensi->foto_masuk) }}" alt="Foto Masuk" class="img-thumbnail" style="max-height: 100px;">
                                        <small class="text-muted d-block">Foto saat ini</small>
                                    </div>
                                @endif
                                <input type="file" name="foto_masuk" class="form-control @error('foto_masuk') is-invalid @enderror" 
                                       accept="image/*">
                                @error('foto_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Foto Pulang</label>
                                @if($absensi->foto_pulang)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $absensi->foto_pulang) }}" alt="Foto Pulang" class="img-thumbnail" style="max-height: 100px;">
                                        <small class="text-muted d-block">Foto saat ini</small>
                                    </div>
                                @endif
                                <input type="file" name="foto_pulang" class="form-control @error('foto_pulang') is-invalid @enderror" 
                                       accept="image/*">
                                @error('foto_pulang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.manual-absensi.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Update
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
        const userSelect = document.getElementById('user_id');
        const statusSelect = document.getElementById('status_kehadiran');
        const jamMasukField = document.getElementById('jam_masuk_field');
        const jamPulangField = document.getElementById('jam_pulang_field');
        const fotoFields = document.getElementById('foto_fields');
        const jamMasukInput = document.getElementById('jam_masuk');
        const jamPulangInput = document.getElementById('jam_pulang');
        const defaultJamMasuk = document.getElementById('default_jam_masuk');
        const defaultJamPulang = document.getElementById('default_jam_pulang');

        function updateJamKerjaInfo() {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            if (selectedOption.value) {
                const jamMasuk = selectedOption.dataset.jamMasuk;
                const jamPulang = selectedOption.dataset.jamPulang;
                defaultJamMasuk.textContent = jamMasuk;
                defaultJamPulang.textContent = jamPulang;
                
                // Set default values if current values are empty
                if (!jamMasukInput.value) {
                    jamMasukInput.value = jamMasuk;
                }
                if (!jamPulangInput.value) {
                    jamPulangInput.value = jamPulang;
                }
            }
        }

        function toggleFields() {
            const status = statusSelect.value;
            if (status === 'hadir') {
                jamMasukField.style.display = 'block';
                jamPulangField.style.display = 'block';
                fotoFields.style.display = 'block';
            } else {
                jamMasukField.style.display = 'none';
                jamPulangField.style.display = 'none';
                fotoFields.style.display = 'none';
            }
        }

        userSelect.addEventListener('change', updateJamKerjaInfo);
        statusSelect.addEventListener('change', toggleFields);

        // Initial call
        updateJamKerjaInfo();
        toggleFields();
    });
</script>
@endpush