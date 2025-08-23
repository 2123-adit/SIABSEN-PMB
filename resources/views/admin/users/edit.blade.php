@extends('layouts.admin')

@section('title', 'Edit User - ADMA Absensi Kantor')
@section('page-title', 'Edit User')

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
    
    /* PASSWORD STRENGTH INDICATOR */
    .password-strength-indicator {
        margin-top: 5px;
        font-size: 0.875rem;
    }
    .strength-bar {
        height: 4px;
        border-radius: 2px;
        transition: all 0.3s ease;
        margin-top: 5px;
        background-color: #e9ecef;
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
    
    /* USER INFO CARD */
    .user-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- User Info Header -->
        <div class="user-info-card">
            <div class="d-flex align-items-center">
                <div class="user-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h5 class="mb-1">Edit User: {{ $user->name }}</h5>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-person-badge me-1"></i>{{ $user->user_id }} • 
                        <i class="bi bi-briefcase me-1"></i>{{ $user->jabatan->nama_jabatan }} • 
                        <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }} bg-opacity-75">
                            {{ ucfirst($user->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Form Edit User
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">User ID <span class="text-danger">*</span></label>
                                <input type="text" name="user_id" class="form-control @error('user_id') is-invalid @enderror" 
                                       value="{{ old('user_id', $user->user_id) }}" required>
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
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <div class="password-toggle-container">
                                    <input type="password" name="password" id="new_password" 
                                           class="form-control @error('password') is-invalid @enderror"
                                           onkeyup="checkPasswordStrength(this.value)">
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('new_password')" 
                                            title="Toggle password visibility">
                                        <i class="bi bi-eye" id="new_password-toggle-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Password Strength Indicator -->
                                <div class="password-strength-indicator" id="strength-container" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small id="strength-text">Kekuatan password:</small>
                                    </div>
                                    <div class="strength-bar" id="strength-bar"></div>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="password-requirements" id="requirements-container" style="display: none;">
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
                                
                                <div class="form-text">
                                    Kosongkan jika tidak ingin mengubah password
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="generateNewPassword()">
                                        <i class="bi bi-arrow-clockwise"></i> Generate
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="clearPassword()">
                                        <i class="bi bi-x"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select name="jabatan_id" class="form-select @error('jabatan_id') is-invalid @enderror" required>
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" {{ old('jabatan_id', $user->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                            {{ $jabatan->nama_jabatan }}
                                            @if($jabatan->toleransi_terlambat)
                                                (Toleransi: {{ $jabatan->toleransi_terlambat }} menit)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('jabatan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Jabatan saat ini: <strong>{{ $user->jabatan->nama_jabatan }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" {{ old('status', $user->status) == 'aktif' ? 'selected' : '' }}>
                                        <i class="bi bi-check-circle"></i> Aktif
                                    </option>
                                    <option value="nonaktif" {{ old('status', $user->status) == 'nonaktif' ? 'selected' : '' }}>
                                        <i class="bi bi-x-circle"></i> Nonaktif
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Status saat ini: 
                                    <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="time" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" 
                                       value="{{ old('jam_masuk', $user->jam_masuk ? $user->jam_masuk->format('H:i') : '') }}" required>
                                @error('jam_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Saat ini: <strong>{{ $user->jam_masuk ? $user->jam_masuk->format('H:i') : '-' }}</strong></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                <input type="time" name="jam_pulang" class="form-control @error('jam_pulang') is-invalid @enderror" 
                                       value="{{ old('jam_pulang', $user->jam_pulang ? $user->jam_pulang->format('H:i') : '') }}" required>
                                @error('jam_pulang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Saat ini: <strong>{{ $user->jam_pulang ? $user->jam_pulang->format('H:i') : '-' }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- User Statistics Card (Read-only info) -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-1"></i>
                                Statistik User (Read-only)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h5 class="text-primary">{{ $user->persentase_kehadiran }}%</h5>
                                        <small class="text-muted">Kehadiran</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h5 class="text-warning">{{ $user->total_terlambat }}</h5>
                                        <small class="text-muted">Terlambat</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h5 class="text-info">{{ $user->absensis()->count() }}</h5>
                                        <small class="text-muted">Total Absensi</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h5 class="text-success">{{ $user->created_at->diffForHumans() }}</h5>
                                    <small class="text-muted">Bergabung</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali
                            </a>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info ms-2">
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
                                Update User
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
        const strengthContainer = document.getElementById('strength-container');
        const requirementsContainer = document.getElementById('requirements-container');
        
        if (password.length === 0) {
            strengthContainer.style.display = 'none';
            requirementsContainer.style.display = 'none';
            return;
        }
        
        strengthContainer.style.display = 'block';
        requirementsContainer.style.display = 'block';
        
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
        
        if (metRequirements <= 2) {
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
        const icon = element.querySelector('i');
        
        if (isMet) {
            element.className = 'requirement-met';
            icon.className = 'bi bi-check-circle';
        } else {
            element.className = 'requirement-unmet';
            icon.className = 'bi bi-x-circle';
        }
    }
    
    // GENERATE NEW PASSWORD FOR EDIT FORM
    function generateNewPassword() {
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
        document.getElementById('new_password').value = password;
        checkPasswordStrength(password);
        
        // Show password temporarily
        document.getElementById('new_password').type = 'text';
        document.getElementById('new_password-toggle-icon').className = 'bi bi-eye-slash';
        
        // Show notification with copy functionality
        showPasswordNotification(password);
        
        // Copy to clipboard
        navigator.clipboard.writeText(password).then(() => {
            console.log('Password copied to clipboard');
        }).catch(err => {
            console.error('Failed to copy password to clipboard:', err);
        });
        
        // Auto-hide password after 5 seconds
        setTimeout(() => {
            if (document.getElementById('new_password').type === 'text') {
                document.getElementById('new_password').type = 'password';
                document.getElementById('new_password-toggle-icon').className = 'bi bi-eye';
            }
        }, 5000);
    }
    
    // CLEAR PASSWORD FIELD
    function clearPassword() {
        document.getElementById('new_password').value = '';
        checkPasswordStrength('');
        document.getElementById('new_password').type = 'password';
        document.getElementById('new_password-toggle-icon').className = 'bi bi-eye';
    }
    
    // RESET FORM TO ORIGINAL VALUES
    function resetForm() {
        if (confirm('Reset form ke nilai awal? Perubahan yang belum disimpan akan hilang.')) {
            // Reset to original values
            document.querySelector('input[name="username"]').value = '{{ $user->username }}';
            document.querySelector('input[name="name"]').value = '{{ $user->name }}';
            document.querySelector('input[name="password"]').value = '';
            document.querySelector('select[name="jabatan_id"]').value = '{{ $user->jabatan_id }}';
            document.querySelector('select[name="status"]').value = '{{ $user->status }}';
            document.querySelector('input[name="jam_masuk"]').value = '{{ $user->jam_masuk ? $user->jam_masuk->format("H:i") : "" }}';
            document.querySelector('input[name="jam_pulang"]').value = '{{ $user->jam_pulang ? $user->jam_pulang->format("H:i") : "" }}';
            
            // Clear password strength indicators
            checkPasswordStrength('');
        }
    }
    
    // SHOW PASSWORD NOTIFICATION
    function showPasswordNotification(password) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.password-notification');
        existingNotifications.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show mt-2 password-notification';
        notification.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Password baru telah dibuat!</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-success me-2" onclick="copyPasswordAgain('${password}')">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <hr>
            <div class="font-monospace bg-light p-2 rounded">
                <strong>${password}</strong>
            </div>
            <small class="text-muted">Password telah disalin ke clipboard dan akan disembunyikan dalam 5 detik.</small>
        `;
        
        document.querySelector('.password-toggle-container').parentNode.appendChild(notification);
        
        // Auto remove notification after 10 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 10000);
    }
    
    // COPY PASSWORD AGAIN
    function copyPasswordAgain(password) {
        navigator.clipboard.writeText(password).then(() => {
            // Show temporary feedback
            const button = event.target.closest('button');
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check"></i> Copied!';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.disabled = false;
            }, 1500);
        });
    }
    
    // KEYBOARD SHORTCUTS
    document.addEventListener('keydown', function(e) {
        // Ctrl + Shift + P for password toggle
        if (e.ctrlKey && e.shiftKey && e.key === 'P') {
            e.preventDefault();
            togglePassword('new_password');
        }
        
        // Ctrl + Shift + G for generate password
        if (e.ctrlKey && e.shiftKey && e.key === 'G') {
            e.preventDefault();
            generateNewPassword();
        }
        
        // Ctrl + Shift + R for reset form
        if (e.ctrlKey && e.shiftKey && e.key === 'R') {
            e.preventDefault();
            resetForm();
        }
        
        // Ctrl + S for save (prevent default browser save)
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            document.querySelector('form').submit();
        }
    });
    
    // FORM CHANGE DETECTION
    let formChanged = false;
    document.addEventListener('input', function() {
        formChanged = true;
    });
    
    // WARN USER BEFORE LEAVING IF FORM CHANGED
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // MARK FORM AS SAVED WHEN SUBMITTING
    document.querySelector('form').addEventListener('submit', function() {
        formChanged = false;
    });
    
    // INITIALIZE ON PAGE LOAD
    document.addEventListener('DOMContentLoaded', function() {
        // Focus on first input
        document.querySelector('input[name="username"]').focus();
        
        // Initialize password strength if password field has value
        const passwordField = document.getElementById('new_password');
        if (passwordField.value) {
            checkPasswordStrength(passwordField.value);
        }
    });
</script>
@endpush