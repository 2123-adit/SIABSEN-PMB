<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - ADMA Absensi Kantor</title>
    
    <!-- Favicon - Temporary Base64 for testing -->
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAdgAAAHYBTnsmCAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAANUSURBVFiFtZdLiFxVFIa/c2/dqq7X9KQzk4lJJhEyGhQVFVQQwYUgCIKCG1cuBBeCuHLjRnDlzoU7wYWCKwiCCxcuFBQUFYwGH2hQE4ckk5nJdLq7uqvq3nvOcVFdXV3dXZ1JfzBwOef8/3fO/Z//nCuqyv9ZxP8A4Ha7Tc/z3hSRN0WkALSASVX9UlW/2Nvb+6Rer4+JyAURaY/dBRGpqurfqvq1iHyoqgdUdUtE1lR1U0S2RWRdRPZU9aCq7qrqnoi0ROSgiOyLyI6ItEVkX0R2VXVPVTsi0lLVgxE5KCIHVHVXVQ+p6qGqHlLVjojs/w8AAAD//6q6p6p7qrqnqgdU9aCqtlR1X1Vbqrqvql1V3VPVrqp2VLVTX1+fFpEtEWmJyI6qtkVkR0S2RaQlIi0RaYvIloi0VLUlIm0R2VHVbRHZVtWtsbGxm0RkXVVXVXVNVddUdU1V11V1Q1U3RWRTVTdVdVNVN0VkS1U3RWRTVTdUdV1V11R1TVXXRGRdVddUdU1V11V1Q1U3RGRDVddVdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN1R1Q1XXVXVdVddVdU1V11R1TVXXRGRdVddUdU1V11V1Q1XXVXVdVTdUdUNVN0RkQ1U3RGRDVTdUdUNVN/8AAP//">
    <!-- Fallback favicon links -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Inter Font Global */
        body, .login-card, .form-control, .btn, h1, h2, h3, h4, h5, h6, p, span, div, a, label {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
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
                            ADMA
                        </h3>
                        <p class="mb-0 mt-2 opacity-75">Absensi Kantor - Admin Panel</p>
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
                                ADMA - Absensi Kantor<br>
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
