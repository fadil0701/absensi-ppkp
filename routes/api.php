<?php

use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\SatpelkesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// API Key protected routes (untuk sistem-to-system)
Route::middleware('api.key')->group(function () {
    // Integration endpoints
    Route::prefix('integration')->group(function () {
        Route::get('/akumulasi', [IntegrationController::class, 'getAkumulasi']);
        Route::get('/presensi-harian', [IntegrationController::class, 'getPresensiHarian']);
        Route::get('/export', [IntegrationController::class, 'exportData']);
    });
});

// Sanctum protected routes (untuk user authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Presensi
    Route::prefix('presensi')->group(function () {
        Route::post('/check-in', [PresensiController::class, 'checkIn']);
        Route::post('/check-out', [PresensiController::class, 'checkOut']);
        Route::get('/riwayat', [PresensiController::class, 'riwayat']);
        Route::get('/{id}', [PresensiController::class, 'show']);
    });

    // Approval (Pimpinan only)
    Route::prefix('presensi/approval')->middleware('pimpinan')->group(function () {
        Route::get('/pending', [ApprovalController::class, 'pending']);
        Route::post('/approve', [ApprovalController::class, 'approve']);
        Route::post('/reject', [ApprovalController::class, 'reject']);
    });

    // Jadwal
    Route::prefix('jadwal')->group(function () {
        Route::get('/pegawai/{pegawai_id}', [JadwalController::class, 'getJadwal']);
        Route::post('/pegawai', [JadwalController::class, 'store']);
    });

    // Laporan
    Route::prefix('laporan')->group(function () {
        Route::get('/telat', [LaporanController::class, 'telat']);
        Route::get('/tidak-masuk', [LaporanController::class, 'tidakMasuk']);
        Route::get('/dashboard', [LaporanController::class, 'dashboard']);
    });

    // Satpelkes
    Route::get('/satpelkes', [SatpelkesController::class, 'index']);

    // Integration endpoints (untuk bridging dengan sistem lain)
    Route::prefix('integration')->group(function () {
        Route::get('/akumulasi', [IntegrationController::class, 'getAkumulasi']);
        Route::get('/presensi-harian', [IntegrationController::class, 'getPresensiHarian']);
        Route::get('/export', [IntegrationController::class, 'exportData']);
    });
});
