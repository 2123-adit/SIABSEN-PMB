@extends('layouts.admin')

@section('title', 'Tambah Jabatan - SIABSEN PMB')
@section('page-title', 'Tambah Jabatan Baru')

@push('styles')
<style>
    .jadwal-selector {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 15px 0;
        border: 1px solid #e9ecef;
    }
    .jadwal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        margin: -20px -20px 15px -20px;
        font-weight: bold;
    }
    .day-checkbox {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .day-checkbox:hover {
        border-color: #667eea;
        background: #f8f9fa;
    }
    .day-checkbox.selected {
        border-color: #667eea;
        background: #e3f2fd;
    }
    .day-checkbox input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.2);
    }
    .jadwal-preview {
        background: #e8f5e8;
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
        border-left: 4px solid #28a745;
    }
    .tolerance-info {
        background: #fff3cd;
        padding: 10px;
        border-radius: 5px;
        border-left: 4px solid #ffc107;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-briefcase-fill me-2"></i>
                    Form Tambah Jabatan Baru
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tips:</strong> Pastikan nama jabatan unik dan atur jadwal kerja dengan benar. 
                    Pengaturan ini akan mempengaruhi sistem absensi user dengan jabatan tersebut.
                </div>

                <form action="{{ route('admin.jabatan.store') }}" method="POST" id="jabatanForm">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jabatan" 
                                       class="form-control @error('nama_jabatan') is-invalid @enderror" 
                                       value="{{ old('nama_jabatan') }}" 
                                       placeholder="Contoh: Manager IT" required>
                                @error('nama_jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nama jabatan harus unik dan tidak boleh sama dengan yang sudah ada</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Toleransi Terlambat (Menit) <span class="text-danger">*</span></label>
                                <input type="number" name="toleransi_terlambat" 
                                       class="form-control @error('toleransi_terlambat') is-invalid @enderror" 
                                       value="{{ old('toleransi_terlambat', 15) }}" 
                                       min="0" max="60" required>
                                @error('toleransi_terlambat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="tolerance-info">
                                    <i class="bi bi-clock me-1"></i>
                                    <strong>Info:</strong> Karyawan dianggap terlambat jika absen lebih dari toleransi ini
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Jabatan</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                  rows="3" placeholder="Deskripsi singkat tentang jabatan ini (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jadwal Kerja Section -->
                    <div class="jadwal-selector">
                        <div class="jadwal-header">
                            <i class="bi bi-calendar-week me-2"></i>
                            Pengaturan Jadwal Kerja
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Pilih Hari Kerja: <span class="text-danger">*</span></h6>
                                
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
                                    $oldJadwal = old('jadwal_kerja', [
                                        'senin' => true,
                                        'selasa' => true,
                                        'rabu' => true,
                                        'kamis' => true,
                                        'jumat' => true,
                                        'sabtu' => false,
                                        'minggu' => false
                                    ]);
                                @endphp
                                
                                @foreach($days as $key => $day)
                                    <div class="day-checkbox {{ ($oldJadwal[$key] ?? false) ? 'selected' : '' }}" 
                                         onclick="toggleDay('{{ $key }}')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="jadwal_kerja[{{ $key }}]" 
                                                   id="jadwal_{{ $key }}" value="1"
                                                   {{ ($oldJadwal[$key] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="jadwal_{{ $key }}">
                                                {{ $day }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @error('jadwal_kerja')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Quick Select:</h6>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary" onclick="setWeekdays()">
                                        <i class="bi bi-calendar5 me-1"></i>
                                        Senin - Jumat (Weekdays)
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="setSixDays()">
                                        <i class="bi bi-calendar-week me-1"></i>
                                        Senin - Sabtu (6 Hari)
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="setAllDays()">
                                        <i class="bi bi-calendar-fill me-1"></i>
                                        Setiap Hari (7 Hari)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearAllDays()">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Kosongkan Semua
                                    </button>
                                </div>

                                <!-- Live Preview -->
                                <div class="jadwal-preview" id="jadwalPreview">
                                    <h6><i class="bi bi-eye me-1"></i> Preview Jadwal:</h6>
                                    <div id="previewText">Senin - Jumat</div>
                                    <small class="text-muted">Jadwal kerja yang akan diterapkan</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Keterangan Jadwal (Opsional)</label>
                            <input type="text" name="keterangan_jadwal" 
                                   class="form-control @error('keterangan_jadwal') is-invalid @enderror" 
                                   value="{{ old('keterangan_jadwal') }}" 
                                   placeholder="Contoh: Shift pagi, Shift malam, dll">
                            @error('keterangan_jadwal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.jabatan.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Jabatan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Form change detection
    let formChanged = false;
    
    document.addEventListener('input', function() {
        formChanged = true;
    });
    
    document.addEventListener('change', function() {
        formChanged = true;
    });

    // Toggle day selection
    function toggleDay(day) {
        const checkbox = document.getElementById('jadwal_' + day);
        const dayBox = checkbox.closest('.day-checkbox');
        
        checkbox.checked = !checkbox.checked;
        
        if (checkbox.checked) {
            dayBox.classList.add('selected');
        } else {
            dayBox.classList.remove('selected');
        }
        
        updatePreview();
        formChanged = true;
    }

    // Quick select functions
    function setWeekdays() {
        const weekdays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        const allDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        
        allDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            const dayBox = checkbox.closest('.day-checkbox');
            
            if (weekdays.includes(day)) {
                checkbox.checked = true;
                dayBox.classList.add('selected');
            } else {
                checkbox.checked = false;
                dayBox.classList.remove('selected');
            }
        });
        
        updatePreview();
        formChanged = true;
    }

    function setSixDays() {
        const sixDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        const allDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        
        allDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            const dayBox = checkbox.closest('.day-checkbox');
            
            if (sixDays.includes(day)) {
                checkbox.checked = true;
                dayBox.classList.add('selected');
            } else {
                checkbox.checked = false;
                dayBox.classList.remove('selected');
            }
        });
        
        updatePreview();
        formChanged = true;
    }

    function setAllDays() {
        const allDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        
        allDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            const dayBox = checkbox.closest('.day-checkbox');
            
            checkbox.checked = true;
            dayBox.classList.add('selected');
        });
        
        updatePreview();
        formChanged = true;
    }

    function clearAllDays() {
        const allDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        
        allDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            const dayBox = checkbox.closest('.day-checkbox');
            
            checkbox.checked = false;
            dayBox.classList.remove('selected');
        });
        
        updatePreview();
        formChanged = true;
    }

    // Update preview display
    function updatePreview() {
        const selectedDays = [];
        const dayNames = {
            'senin': 'Sen',
            'selasa': 'Sel',
            'rabu': 'Rab',
            'kamis': 'Kam',
            'jumat': 'Jum',
            'sabtu': 'Sab',
            'minggu': 'Min'
        };
        
        Object.keys(dayNames).forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            if (checkbox.checked) {
                selectedDays.push(dayNames[day]);
            }
        });
        
        const previewText = document.getElementById('previewText');
        
        if (selectedDays.length === 0) {
            previewText.textContent = 'Tidak ada hari kerja dipilih';
            previewText.className = 'text-danger';
        } else if (selectedDays.length === 7) {
            previewText.textContent = 'Setiap Hari (7 hari)';
            previewText.className = 'text-success fw-bold';
        } else if (selectedDays.length === 5 && !selectedDays.includes('Sab') && !selectedDays.includes('Min')) {
            previewText.textContent = 'Senin - Jumat (5 hari)';
            previewText.className = 'text-primary fw-bold';
        } else if (selectedDays.length === 6 && !selectedDays.includes('Min')) {
            previewText.textContent = 'Senin - Sabtu (6 hari)';
            previewText.className = 'text-info fw-bold';
        } else {
            previewText.textContent = selectedDays.join(', ') + ` (${selectedDays.length} hari)`;
            previewText.className = 'text-dark fw-bold';
        }
    }

    // Reset form
    function resetForm() {
        if (confirm('Reset form ke nilai default? Semua perubahan akan hilang.')) {
            document.getElementById('jabatanForm').reset();
            setWeekdays(); // Set default to weekdays
            formChanged = false;
        }
    }

    // Form validation
    document.getElementById('jabatanForm').addEventListener('submit', function(e) {
        const selectedDays = document.querySelectorAll('input[name^="jadwal_kerja"]:checked').length;
        
        if (selectedDays === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 hari kerja untuk jabatan ini.');
            return false;
        }
        
        formChanged = false; // Mark as saved
    });

    // Warn before leaving if form changed
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updatePreview();
        
        // Focus on first input
        document.querySelector('input[name="nama_jabatan"]').focus();
    });
</script>
@endpush