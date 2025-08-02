<?php
// routes/api.php - UPDATED

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API Routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/server-time', [AuthController::class, 'serverTime']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/auth/server-time', [AuthController::class, 'serverTime']);
    
    // Absensi
    Route::prefix('absensi')->group(function () {
        Route::get('/dashboard', [AbsensiController::class, 'dashboard']);
        Route::post('/masuk', [AbsensiController::class, 'absenMasuk']);
        Route::post('/pulang', [AbsensiController::class, 'absenPulang']);
        Route::get('/kalender', [AbsensiController::class, 'kalender']);
        Route::get('/riwayat', [AbsensiController::class, 'riwayat']); // NEW: dengan filter
        Route::get('/server-time', [AbsensiController::class, 'serverTime']);
    });
    
    // Geofencing
    Route::prefix('geofencing')->group(function () {
        Route::post('/check', [AbsensiController::class, 'checkGeofencing']);
    });
});

