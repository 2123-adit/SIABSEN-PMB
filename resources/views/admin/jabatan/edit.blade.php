@extends('layouts.admin')

@section('title', 'Edit Jabatan - SIABSEN PMB')
@section('page-title', 'Edit Jabatan')

@push('styles')
<style>
    .jabatan-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
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
    .current-users-info {
        background: #d1ecf1;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
        margin-top: 15px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Jabatan Info Header -->
        <div class="jabatan-info-card">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-briefcase-fill" style="font-size: 2rem;"></i>
                </div>
                <div>
                    <h5 class="mb-1">Edit Jabatan: {{ $jabatan->nama_jabatan }}</h5>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-people me-1"></i>{{ $jabatan->users->where('status', 'aktif')->count() }} User Aktif • 
                        <i class="bi bi-clock me-1"></i>Toleransi: {{ $jabatan->toleransi_terlambat }} menit • 
                        <i class="bi bi-calendar-week me-1"></i>{{ $jabatan->jadwal_display ?? 'Custom Schedule' }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Form Edit Jabatan
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Perubahan jadwal kerja akan mempengaruhi sistem absensi untuk 
                    {{ $jabatan->users->where('status', 'aktif')->count() }} user yang menggunakan jabatan ini.
                </div>

                <form action="{{ route('admin.jabatan.update', $jabatan) }}" method="POST" id="jabatanForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jabatan" 
                                       class="form-control @error('nama_jabatan') is-invalid @enderror" 
                                       value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}" 
                                       placeholder="Contoh: Manager IT" required>
                                @error('nama_jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Nama saat ini: <strong>{{ $jabatan->nama_jabatan }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Toleransi Terlambat (Menit) <span class="text-danger">*</span></label>
                                <input type="number" name="toleransi_terlambat" 
                                       class="form-control @error('toleransi_terlambat') is-invalid @enderror" 
                                       value="{{ old('toleransi_terlambat', $jabatan->toleransi_terlambat) }}" 
                                       min="0" max="60" required>
                                @error('toleransi_terlambat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="tolerance-info">
                                    <i class="bi bi-clock me-1"></i>
                                    <strong>Saat ini:</strong> {{ $jabatan->toleransi_terlambat }} menit
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Jabatan</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                  rows="3" placeholder="Deskripsi singkat tentang jabatan ini (opsional)">{{ old('deskripsi', $jabatan->deskripsi) }}</textarea>
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
                                    $currentJadwal = old('jadwal_kerja', $jabatan->jadwal_kerja ?? []);
                                @endphp
                                
                                @foreach($days as $key => $day)
                                    <div class="day-checkbox {{ ($currentJadwal[$key] ?? false) ? 'selected' : '' }}" 
                                         onclick="toggleDay('{{ $key }}')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="jadwal_kerja[{{ $key }}]" 
                                                   id="jadwal_{{ $key }}" value="1"
                                                   {{ ($currentJadwal[$key] ?? false) ? 'checked' : '' }}>
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
                                    <div id="previewText">{{ $jabatan->jadwal_display ?? 'Custom Schedule' }}</div>
                                    <small class="text-muted">Jadwal kerja yang akan diterapkan</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Keterangan Jadwal (Opsional)</label>
                            <input type="text" name="keterangan_jadwal" 
                                   class="form-control @error('keterangan_jadwal') is-invalid @enderror" 
                                   value="{{ old('keterangan_jadwal', $jabatan->keterangan_jadwal) }}" 
                                   placeholder="Contoh: Shift pagi, Shift malam, dll">
                            @error('keterangan_jadwal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Current Users Info -->
                    @if($jabatan->users->where('status', 'aktif')->count() > 0)
                        <div class="current-users-info">
                            <h6>
                                <i class="bi bi-people me-1"></i>
                                User yang Menggunakan Jabatan Ini ({{ $jabatan->users->where('status', 'aktif')->count() }} orang)
                            </h6>
                            <div class="row">
                                @foreach($jabatan->users->where('status', 'aktif')->take(6) as $user)
                                    <div class="col-md-4">
                                        <small>
                                            <i class="bi bi-person me-1"></i>
                                            {{ $user->name }}
                                        </small>
                                    </div>
                                @endforeach
                                @if($jabatan->users->where('status', 'aktif')->count() > 6)
                                    <div class="col-md-4">
                                        <small class="text-muted">
                                            ... dan {{ $jabatan->users->where('status', 'aktif')->count() - 6 }} lainnya
                                        </small>
                                    </div>
                                @endif
                            </div>
                            <small class="text-info mt-2 d-block">
                                <i class="bi bi-info-circle me-1"></i>
                                Perubahan jadwal akan mempengaruhi absensi semua user ini
                            </small>
                        </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.jabatan.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali
                            </a>
                            <a href="{{ route('admin.jabatan.show', $jabatan) }}" class="btn btn-outline-info ms-2">
                                <i class="bi bi-eye me-1"></i>
                                Lihat Detail
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Update Jabatan
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
    let originalFormData = {};
    
    // Store original form data on page load
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('jabatanForm');
        originalFormData = new FormData(form);
        updatePreview();
        
        // Add change listeners
        form.addEventListener('input', function() {
            formChanged = true;
        });
        
        form.addEventListener('change', function() {
            formChanged = true;
            updatePreview();
        });
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
        
        formChanged = true;
        updatePreview();
    }

    // Quick select functions
    function setWeekdays() {
        const weekdays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        clearAllDays();
        weekdays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            checkbox.checked = true;
            checkbox.closest('.day-checkbox').classList.add('selected');
        });
        formChanged = true;
        updatePreview();
    }

    function setSixDays() {
        const sixDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        clearAllDays();
        sixDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            checkbox.checked = true;
            checkbox.closest('.day-checkbox').classList.add('selected');
        });
        formChanged = true;
        updatePreview();
    }

    function setAllDays() {
        const allDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        allDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            checkbox.checked = true;
            checkbox.closest('.day-checkbox').classList.add('selected');
        });
        formChanged = true;
        updatePreview();
    }

    function clearAllDays() {
        const allDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        allDays.forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            checkbox.checked = false;
            checkbox.closest('.day-checkbox').classList.remove('selected');
        });
        formChanged = true;
        updatePreview();
    }

    // Update preview jadwal
    function updatePreview() {
        const days = {
            'senin': 'Senin',
            'selasa': 'Selasa',
            'rabu': 'Rabu',
            'kamis': 'Kamis',
            'jumat': 'Jumat',
            'sabtu': 'Sabtu',
            'minggu': 'Minggu'
        };
        
        const selectedDays = [];
        Object.keys(days).forEach(day => {
            const checkbox = document.getElementById('jadwal_' + day);
            if (checkbox.checked) {
                selectedDays.push(days[day]);
            }
        });
        
        const previewText = document.getElementById('previewText');
        if (selectedDays.length === 0) {
            previewText.textContent = 'Tidak ada hari kerja dipilih';
            previewText.className = 'text-danger';
        } else if (selectedDays.length === 5 && 
                   selectedDays.includes('Senin') && 
                   selectedDays.includes('Selasa') && 
                   selectedDays.includes('Rabu') && 
                   selectedDays.includes('Kamis') && 
                   selectedDays.includes('Jumat')) {
            previewText.textContent = 'Senin - Jumat (Weekdays)';
            previewText.className = 'text-success';
        } else if (selectedDays.length === 6 && !selectedDays.includes('Minggu')) {
            previewText.textContent = 'Senin - Sabtu (6 Hari Kerja)';
            previewText.className = 'text-success';
        } else if (selectedDays.length === 7) {
            previewText.textContent = 'Setiap Hari (7 Hari Kerja)';
            previewText.className = 'text-info';
        } else {
            previewText.textContent = selectedDays.join(', ');
            previewText.className = 'text-primary';
        }
    }

    // Reset form to original state
    function resetForm() {
        if (confirm('Apakah Anda yakin ingin mereset form ke nilai awal?')) {
            location.reload();
        }
    }

    // Form submission validation
    document.getElementById('jabatanForm').addEventListener('submit', function(e) {
        const selectedDays = document.querySelectorAll('input[name^="jadwal_kerja"]:checked');
        
        if (selectedDays.length === 0) {
            e.preventDefault();
            alert('Silakan pilih minimal satu hari kerja!');
            document.querySelector('.jadwal-selector').scrollIntoView({
                behavior: 'smooth'
            });
            return false;
        }
        
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses...';
    });

    // Warn user before leaving if form has changes
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });

    // Remove beforeunload listener when form is submitted
    document.getElementById('jabatanForm').addEventListener('submit', function() {
        window.removeEventListener('beforeunload', arguments.callee);
    });

    // Auto-save draft functionality (optional)
    let autoSaveTimer;
    function autoSaveDraft() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            if (formChanged && localStorage) {
                const formData = new FormData(document.getElementById('jabatanForm'));
                const draftData = {};
                for (let [key, value] of formData.entries()) {
                    draftData[key] = value;
                }
                localStorage.setItem('jabatan_edit_draft_{{ $jabatan->id }}', JSON.stringify(draftData));
            }
        }, 2000);
    }

    // Listen for form changes to trigger auto-save
    document.getElementById('jabatanForm').addEventListener('input', autoSaveDraft);
    document.getElementById('jabatanForm').addEventListener('change', autoSaveDraft);

    // Load draft on page load if exists
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage) {
            const draftData = localStorage.getItem('jabatan_edit_draft_{{ $jabatan->id }}');
            if (draftData && confirm('Ditemukan draft perubahan yang belum disimpan. Muat draft tersebut?')) {
                const draft = JSON.parse(draftData);
                Object.keys(draft).forEach(key => {
                    const element = document.querySelector(`[name="${key}"]`);
                    if (element) {
                        if (element.type === 'checkbox') {
                            element.checked = draft[key] === '1';
                            if (element.checked) {
                                element.closest('.day-checkbox').classList.add('selected');
                            }
                        } else {
                            element.value = draft[key];
                        }
                    }
                });
                updatePreview();
                formChanged = true;
            }
        }
    });

    // Clear draft when form is successfully submitted
    document.getElementById('jabatanForm').addEventListener('submit', function() {
        if (localStorage) {
            localStorage.removeItem('jabatan_edit_draft_{{ $jabatan->id }}');
        }
    });
</script>
@endpush