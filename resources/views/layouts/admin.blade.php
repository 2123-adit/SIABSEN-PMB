<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIABSEN PMB')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            margin: 0.125rem 0;
            border-radius: 0.375rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .navbar-brand {
            font-weight: 700;
            color: #2c3e50 !important;
        }
        /* NEW: Menu group separator */
        .menu-separator {
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 0.5rem 0;
        }
        .menu-group-title {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 1rem 0 0.5rem 0;
            padding: 0 1rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white fw-bold">SIABSEN PMB</h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <!-- Master Data Section -->
                        <div class="menu-group-title">Master Data</div>
                        
                        <!-- NEW: Jabatan Management -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.jabatan.*') ? 'active' : '' }}" 
                               href="{{ route('admin.jabatan.index') }}">
                                <i class="bi bi-briefcase me-2"></i>
                                Manajemen Jabatan
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                               href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people me-2"></i>
                                Manajemen User
                            </a>
                        </li>
                        
                        <!-- Absensi Section -->
                        <div class="menu-group-title">Absensi</div>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.absensi.*') ? 'active' : '' }}" 
                               href="{{ route('admin.absensi.index') }}">
                                <i class="bi bi-calendar-check me-2"></i>
                                Data Absensi
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.manual-absensi.*') ? 'active' : '' }}" 
                               href="{{ route('admin.manual-absensi.index') }}">
                                <i class="bi bi-calendar-plus me-2"></i>
                                Absensi Manual
                            </a>
                        </li>
                        
                        <!-- Reports Section -->
                        <div class="menu-group-title">Laporan</div>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" 
                               href="{{ route('admin.laporan.index') }}">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Generate Laporan
                            </a>
                        </li>
                        
                        <!-- Settings Section -->
                        <div class="menu-group-title">Pengaturan</div>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}" 
                               href="{{ route('admin.holidays.index') }}">
                                <i class="bi bi-calendar-x me-2"></i>
                                Hari Libur
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.geofencing.*') ? 'active' : '' }}" 
                               href="{{ route('admin.geofencing.index') }}">
                                <i class="bi bi-geo-alt me-2"></i>
                                Geofencing
                            </a>
                        </li>
                        
                        <div class="menu-separator"></div>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="confirmLogout(event)">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top navbar -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="navbar-text">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ auth()->user()->name }}
                                <span class="badge bg-primary ms-1">{{ auth()->user()->jabatan->nama_jabatan ?? 'Admin' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
                
                <!-- Footer -->
                <footer class="mt-5 py-4 border-top">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-0">
                                &copy; {{ date('Y') }} SIABSEN PMB. All rights reserved.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <p class="text-muted small mb-0">
                                <i class="bi bi-clock me-1"></i>
                                Server Time: <span id="server-time">{{ now('Asia/Jakarta')->format('d/m/Y H:i:s') }}</span> WIB
                            </p>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Logout confirmation function
        function confirmLogout(event) {
            event.preventDefault();
            
            // Custom confirmation dialog with better styling
            if (confirm('🚪 Apakah Anda yakin ingin logout dari sistem?\n\n✅ Klik OK untuk logout\n❌ Klik Cancel untuk tetap login')) {
                document.getElementById('logout-form').submit();
            }
        }
        
        // Initialize DataTables
        $(document).ready(function() {
            $('.table-datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                responsive: true,
                pageLength: 25,
                order: []
            });
        });
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Add smooth transitions for sidebar links
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
                this.style.transition = 'all 0.3s ease';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
        
        // Update server time every minute
        function updateServerTime() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Jakarta',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const timeString = now.toLocaleDateString('id-ID', options);
            const serverTimeElement = document.getElementById('server-time');
            if (serverTimeElement) {
                serverTimeElement.textContent = timeString;
            }
        }
        
        // Update time every minute
        setInterval(updateServerTime, 60000);
        
        // Global helper function for showing alerts
        window.showAlert = function(type, message, duration = 5000) {
            const alertTypes = {
                'success': { icon: 'check-circle', class: 'alert-success' },
                'error': { icon: 'exclamation-triangle', class: 'alert-danger' },
                'warning': { icon: 'exclamation-triangle', class: 'alert-warning' },
                'info': { icon: 'info-circle', class: 'alert-info' }
            };
            
            const alertConfig = alertTypes[type] || alertTypes['info'];
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertConfig.class} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="bi bi-${alertConfig.icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of main content
            const mainContent = document.querySelector('main');
            const firstChild = mainContent.querySelector('.d-flex');
            mainContent.insertBefore(alertDiv, firstChild.nextSibling);
            
            // Auto remove after specified duration
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        };
    </script>
    
    @stack('scripts')
</body>
</html>