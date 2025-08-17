<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SIABSEN PMB</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-body {
            padding: 2rem;
        }

        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .timezone-info {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.875rem;
        }

        .password-toggle-btn {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            z-index: 5;
        }

        .password-toggle-btn:hover {
            color: #495057;
        }

        .password-toggle-btn:focus {
            outline: none;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <h3 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            SIABSEN PMB
                        </h3>
                        <p class="mb-0 mt-2 opacity-75">Admin Panel Login</p>
                    </div>

                    <div class="login-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- User ID -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control @error('user_id') is-invalid @enderror"
                                       id="user_id" name="user_id" placeholder="User ID" value="{{ old('user_id') }}" required autofocus>
                                <label for="user_id">
                                    <i class="bi bi-person me-2"></i>User ID
                                </label>
                            </div>

                            <!-- Password with toggle -->
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" placeholder="Password" required>
                                <label for="password">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <button type="button" class="password-toggle-btn" onclick="togglePassword('password')" 
                                        title="Toggle password visibility">
                                    <i class="bi bi-eye" id="password-toggle-icon"></i>
                                </button>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">
                                    Ingat saya
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </button>
                        </form>

                        <div class="timezone-info">
                            <i class="bi bi-clock me-1"></i>
                            <strong>Zona Waktu:</strong> Asia/Jakarta (WIB)<br>
                            <span id="current-time"></span>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Sistem Informasi Absensi PMB<br>
                                &copy; {{ date('Y') }} All rights reserved
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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

        function updateTime() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('current-time').textContent = now.toLocaleDateString('id-ID', options);
        }

        updateTime();
        setInterval(updateTime, 1000);

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                togglePassword('password');
            }
        });
    </script>
</body>
</html>
