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
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/profile/upload-photo', [AuthController::class, 'uploadProfilePhoto']);
    Route::get('/auth/server-time', [AuthController::class, 'serverTime']);
    
    // Absensi
    Route::prefix('absensi')->group(function () {
        Route::get('/dashboard', [AbsensiController::class, 'dashboard']);
        Route::post('/masuk', [AbsensiController::class,'absenMasuk'])->middleware('throttle:5,1');
        Route::post('/pulang', [AbsensiController::class,'absenPulang'])->middleware('throttle:5,1');
        Route::get('/kalender', [AbsensiController::class, 'kalender']);
        Route::get('/riwayat', [AbsensiController::class, 'riwayat']); // NEW: dengan filter
        Route::get('/server-time', [AbsensiController::class, 'serverTime']);
        Route::post('/check-geofence', [AbsensiController::class, 'checkGeofence']);
    });
    
    // Geofencing
    Route::prefix('geofencing')->group(function () {
        Route::get('/settings', [AbsensiController::class, 'getGeofencingSettings']);
        Route::post('/check', [AbsensiController::class, 'checkGeofencing']);
    });
});

