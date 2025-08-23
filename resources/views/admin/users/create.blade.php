@extends('layouts.admin')

@section('title', 'Tambah User - ADMA Absensi Kantor')
@section('page-title', 'Tambah User')

@push('styles')
<style>
    /* PASSWORD TOGGLE STYLES */
    .password-toggle-container {
        position: relative;
    }
    .password-toggle-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 4px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10;
        border-radius: 4px;
    }
    .password-toggle-btn:hover {
        color: #495057;
        background-color: #f8f9fa;
    }
    .password-toggle-btn:focus {
        outline: none;
        color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .password-strength-indicator {
        margin-top: 5px;
        font-size: 0.875rem;
    }
    .strength-bar {
        height: 4px;
        border-radius: 2px;
        transition: all 0.3s ease;
        margin-top: 5px;
    }
    .strength-weak { background-color: #dc3545; width: 25%; }
    .strength-fair { background-color: #ffc107; width: 50%; }
    .strength-good { background-color: #17a2b8; width: 75%; }
    .strength-strong { background-color: #28a745; width: 100%; }
    
    .password-requirements {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 5px;
    }
    .password-requirements ul {
        margin: 0;
        padding-left: 1rem;
    }
    .password-requirements li {
        margin-bottom: 2px;
    }
    .requirement-met {
        color: #28a745;
    }
    .requirement-unmet {
        color: #dc3545;
    }
    
    /* NEW: Jadwal selector styles */
    .jadwal-selector {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
    }
    .jadwal-info {
        background: #e3f2fd;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 0.875rem;
        border-left: 4px solid #2196f3;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>
                    Form Tambah User
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">User ID <span class="text-danger">*</span></label>
                                <input type="text" name="user_id" class="form-control @error('user_id') is-invalid @enderror" 
                                       value="{{ old('user_id') }}" required>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">User ID untuk login (tanpa spasi)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="password-toggle-container">
                                    <input type="password" name="password" id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           required onkeyup="checkPasswordStrength(this.value)">
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password')" 
                                            title="Toggle password visibility">
                                        <i class="bi bi-eye" id="password-toggle-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Password Strength Indicator -->
                                <div class="password-strength-indicator">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small id="strength-text">Kekuatan password:</small>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="generatePassword()" title="Generate secure password">
                                            <i class="bi bi-arrow-clockwise"></i> Generate
                                        </button>
                                    </div>
                                    <div class="strength-bar" id="strength-bar"></div>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="password-requirements">
                                    <ul id="password-requirements">
                                        <li id="req-length" class="requirement-unmet">
                                            <i class="bi bi-x-circle"></i> Minimal 8 karakter
                                        </li>
                                        <li id="req-lowercase" class="requirement-unmet">
                                            <i class="bi bi-x-circle"></i> Huruf kecil (a-z)
                                        </li>
                                        <li id="req-uppercase" class="requirement-unmet">
                                            <i class="bi bi-x-circle"></i> Huruf besar (A-Z)
                                        </li>
                                        <li id="req-number" class="requirement-unmet">
                                            <i class="bi bi-x-circle"></i> Angka (0-9)
                                        </li>
                                        <li id="req-special" class="requirement-unmet">
                                            <i class="bi bi-x-circle"></i> Karakter khusus (@$!%*?&)
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select name="jabatan_id" id="jabatan_id" class="form-select @error('jabatan_id') is-invalid @enderror" required>
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" 
                                                data-jadwal="{{ $jabatan->jadwal_display ?? '' }}"
                                                {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                            {{ $jabatan->nama_jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jabatan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="jadwal-info" id="jadwal-info" style="display: none;">
                                    <i class="bi bi-calendar-week me-1"></i>
                                    <strong>Jadwal Kerja:</strong> <span id="jadwal-text">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="time" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" 
                                       value="{{ old('jam_masuk', '08:00') }}" required>
                                @error('jam_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                <input type="time" name="jam_pulang" class="form-control @error('jam_pulang') is-invalid @enderror" 
                                       value="{{ old('jam_pulang', '17:00') }}" required>
                                @error('jam_pulang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Simpan
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
    // NEW: Show jadwal info when jabatan selected
    document.getElementById('jabatan_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const jadwalInfo = document.getElementById('jadwal-info');
        const jadwalText = document.getElementById('jadwal-text');
        
        if (selectedOption.value && selectedOption.dataset.jadwal) {
            jadwalText.textContent = selectedOption.dataset.jadwal;
            jadwalInfo.style.display = 'block';
        } else {
            jadwalInfo.style.display = 'none';
        }
    });

    // PASSWORD TOGGLE FUNCTION
    function togglePassword(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.className = 'bi bi-eye-slash';
        } else {
            passwordField.type = 'password';
            toggleIcon.className = 'bi bi-eye';
        }
    }
    
    // PASSWORD STRENGTH CHECKER
    function checkPasswordStrength(password) {
        const requirements = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[@$!%*?&]/.test(password)
        };
        
        // Update requirement indicators
        updateRequirement('req-length', requirements.length);
        updateRequirement('req-lowercase', requirements.lowercase);
        updateRequirement('req-uppercase', requirements.uppercase);
        updateRequirement('req-number', requirements.number);
        updateRequirement('req-special', requirements.special);
        
        // Calculate strength
        const metRequirements = Object.values(requirements).filter(Boolean).length;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        if (!strengthBar || !strengthText) return; // Safety check
        
        if (password.length === 0) {
            strengthBar.className = 'strength-bar';
            strengthText.textContent = 'Kekuatan password:';
        } else if (metRequirements <= 2) {
            strengthBar.className = 'strength-bar strength-weak';
            strengthText.textContent = 'Kekuatan password: Lemah';
        } else if (metRequirements === 3) {
            strengthBar.className = 'strength-bar strength-fair';
            strengthText.textContent = 'Kekuatan password: Cukup';
        } else if (metRequirements === 4) {
            strengthBar.className = 'strength-bar strength-good';
            strengthText.textContent = 'Kekuatan password: Baik';
        } else {
            strengthBar.className = 'strength-bar strength-strong';
            strengthText.textContent = 'Kekuatan password: Kuat';
        }
    }
    
    // UPDATE REQUIREMENT INDICATOR
    function updateRequirement(elementId, isMet) {
        const element = document.getElementById(elementId);
        if (!element) return; // Safety check
        
        const icon = element.querySelector('i');
        
        if (isMet) {
            element.className = 'requirement-met';
            if (icon) icon.className = 'bi bi-check-circle';
        } else {
            element.className = 'requirement-unmet';
            if (icon) icon.className = 'bi bi-x-circle';
        }
    }
    
    // GENERATE SECURE PASSWORD
    function generatePassword() {
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const symbols = '@$!%*?&';
        
        let password = '';
        
        // Ensure at least one character from each requirement
        password += uppercase[Math.floor(Math.random() * uppercase.length)];
        password += lowercase[Math.floor(Math.random() * lowercase.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];
        
        // Fill the rest with random characters
        const allChars = uppercase + lowercase + numbers + symbols;
        for (let i = 4; i < 12; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }
        
        // Shuffle the password
        password = password.split('').sort(() => Math.random() - 0.5).join('');
        
        // Set the password and check strength
        document.getElementById('password').value = password;
        checkPasswordStrength(password);
        
        // Show notification
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show mt-2';
        notification.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>
            Password aman telah dibuat! Salin: <strong>${password}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.password-toggle-container').parentNode;
        container.appendChild(notification);
        
        // Auto remove notification after 10 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 10000);
        
        // Copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(password).then(() => {
                console.log('Password copied to clipboard');
            });
        }
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + Shift + P for password toggle
        if (e.ctrlKey && e.shiftKey && e.key === 'P') {
            e.preventDefault();
            togglePassword('password');
        }
        
        // Ctrl + Shift + G for generate password
        if (e.ctrlKey && e.shiftKey && e.key === 'G') {
            e.preventDefault();
            generatePassword();
        }
    });
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('password');
        if (passwordField && passwordField.value) {
            checkPasswordStrength(passwordField.value);
        }
        
        // Trigger jadwal display if jabatan already selected
        const jabatanSelect = document.getElementById('jabatan_id');
        if (jabatanSelect.value) {
            jabatanSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush