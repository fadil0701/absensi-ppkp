<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\SatpelkesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
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
});

