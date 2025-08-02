<?php
// routes/web.php - COMPLETE with Jabatan Management and Security

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\ManualAbsensiController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\GeofencingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Privacy Policy Route (Public - untuk Play Store)
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

// Authentication Routes (UPDATED with security)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1'); // ✅ Diperbaiki: Batas maksimal 5 percobaan per menit
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes (Protected)
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Jabatan Management
    Route::prefix('jabatan')->name('jabatan.')->group(function () {
        Route::get('/', [JabatanController::class, 'index'])->name('index');
        Route::get('/create', [JabatanController::class, 'create'])->name('create');
        Route::post('/', [JabatanController::class, 'store'])->name('store');
        Route::get('/{jabatan}', [JabatanController::class, 'show'])->name('show');
        Route::get('/{jabatan}/edit', [JabatanController::class, 'edit'])->name('edit');
        Route::put('/{jabatan}', [JabatanController::class, 'update'])->name('update');
        Route::delete('/{jabatan}', [JabatanController::class, 'destroy'])->name('destroy');
        Route::get('/{jabatan}/users', [JabatanController::class, 'getUsers'])->name('users');
        Route::post('/{jabatan}/bulk-move-users', [JabatanController::class, 'bulkMoveUsers'])->name('bulk-move-users');
    });
    
    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Absensi Management
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::get('/{absensi}', [AbsensiController::class, 'show'])->name('show');
        Route::get('/{absensi}/edit', [AbsensiController::class, 'edit'])->name('edit');
        Route::put('/{absensi}', [AbsensiController::class, 'update'])->name('update');
        Route::delete('/{absensi}', [AbsensiController::class, 'destroy'])->name('destroy');
        Route::get('/kalender/view', [AbsensiController::class, 'calendar'])->name('calendar');
    });
    
    // Manual Absensi Management
    Route::prefix('manual-absensi')->name('manual-absensi.')->group(function () {
        Route::get('/', [ManualAbsensiController::class, 'index'])->name('index');
        Route::get('/create', [ManualAbsensiController::class, 'create'])->name('create');
        Route::post('/', [ManualAbsensiController::class, 'store'])->name('store');
        Route::get('/{absensi}/edit', [ManualAbsensiController::class, 'edit'])->name('edit');
        Route::put('/{absensi}', [ManualAbsensiController::class, 'update'])->name('update');
        Route::delete('/{absensi}', [ManualAbsensiController::class, 'destroy'])->name('destroy');
        Route::get('/bulk/create', [ManualAbsensiController::class, 'bulkCreate'])->name('bulk-create');
        Route::post('/bulk/store', [ManualAbsensiController::class, 'bulkStore'])->name('bulk-store');
        Route::get('/users-by-jabatan', [ManualAbsensiController::class, 'getUserByJabatan'])->name('users-by-jabatan');
    });
    
    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::post('/generate', [LaporanController::class, 'generate'])->name('generate');
        Route::post('/slip', [LaporanController::class, 'slip'])->name('slip');
        Route::get('/statistik', [LaporanController::class, 'statistik'])->name('statistik');
    });
    
    // Holiday Management
    Route::resource('holidays', HolidayController::class);
    Route::patch('holidays/{holiday}/toggle', [HolidayController::class, 'toggle'])->name('holidays.toggle');
    
    // Geofencing Management
    Route::prefix('geofencing')->name('geofencing.')->group(function () {
        Route::get('/', [GeofencingController::class, 'index'])->name('index');
        Route::get('/create', [GeofencingController::class, 'create'])->name('create');
        Route::post('/', [GeofencingController::class, 'store'])->name('store');
        Route::get('/{geofencing}/edit', [GeofencingController::class, 'edit'])->name('edit');
        Route::put('/{geofencing}', [GeofencingController::class, 'update'])->name('update');
        Route::delete('/{geofencing}', [GeofencingController::class, 'destroy'])->name('destroy');
        Route::patch('/{geofencing}/toggle', [GeofencingController::class, 'toggle'])->name('toggle');
        Route::post('/test-location', [GeofencingController::class, 'testLocation'])->name('test-location');
    });
});
