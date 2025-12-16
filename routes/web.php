<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\SatpelkesController;
use App\Http\Controllers\Admin\PresensiController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\TugasLuarController;
use App\Http\Controllers\Admin\JadwalPegawaiController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\IzinCutiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Storage file route (untuk serve file storage jika symlink tidak bekerja)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    if (!$mimeType) {
        $mimeType = 'application/octet-stream';
    }
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
    ]);
})->where('path', '.*')->name('storage.file');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth:web')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Profile Pegawai (All Roles)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Pegawai (Admin & Pimpinan only)
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::resource('pegawai', PegawaiController::class);
    });
    
    // Satpelkes (Admin & Pimpinan only)
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::resource('satpelkes', SatpelkesController::class);
    });
    
    // Absensi (All can do)
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])->name('absensi.checkin');
    Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])->name('absensi.checkout');
    
    // Presensi (All can view, Admin/Pimpinan can manage)
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/{id}', [PresensiController::class, 'show'])->name('presensi.show');
    
    // Approval (Pimpinan & Admin only)
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/approval/pending', [ApprovalController::class, 'pending'])->name('approval.pending');
        Route::post('/approval/approve/{id}', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/reject/{id}', [ApprovalController::class, 'reject'])->name('approval.reject');
    });
    
    // Tugas Luar (All can view, Admin/Pimpinan can approve)
    Route::resource('tugas-luar', TugasLuarController::class);
    Route::get('/tugas-luar/pending', [TugasLuarController::class, 'pending'])->name('tugas-luar.pending');
    Route::post('/tugas-luar/approve/{id}', [TugasLuarController::class, 'approve'])->name('tugas-luar.approve');
    Route::post('/tugas-luar/reject/{id}', [TugasLuarController::class, 'reject'])->name('tugas-luar.reject');
    
    // Jadwal Pegawai (Admin & Pimpinan only)
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/jadwal-pegawai', [JadwalPegawaiController::class, 'index'])->name('jadwal-pegawai.index');
        Route::get('/jadwal-pegawai/create', [JadwalPegawaiController::class, 'create'])->name('jadwal-pegawai.create');
        Route::post('/jadwal-pegawai', [JadwalPegawaiController::class, 'store'])->name('jadwal-pegawai.store');
        Route::post('/jadwal-pegawai/store-multiple', [JadwalPegawaiController::class, 'storeMultiple'])->name('jadwal-pegawai.store-multiple');
        Route::post('/jadwal-pegawai/create-bulk', [JadwalPegawaiController::class, 'createBulk'])->name('jadwal-pegawai.create-bulk');
        Route::get('/jadwal-pegawai/pegawai/{pegawai_id}', [JadwalPegawaiController::class, 'show'])->name('jadwal-pegawai.show'); // Show semua jadwal pegawai
        Route::get('/jadwal-pegawai/{id}/edit', [JadwalPegawaiController::class, 'edit'])->name('jadwal-pegawai.edit'); // Edit jadwal tertentu
        Route::put('/jadwal-pegawai/{id}', [JadwalPegawaiController::class, 'update'])->name('jadwal-pegawai.update'); // Update jadwal tertentu
        Route::delete('/jadwal-pegawai/{id}', [JadwalPegawaiController::class, 'destroy'])->name('jadwal-pegawai.destroy'); // Delete jadwal tertentu
    });
    
    // Laporan (Admin & Pimpinan only)
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/telat', [LaporanController::class, 'telat'])->name('laporan.telat');
        Route::get('/laporan/tidak-masuk', [LaporanController::class, 'tidakMasuk'])->name('laporan.tidakMasuk');
        Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
        Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
    });
    
    // Izin/Cuti (Admin & Pimpinan only)
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/izin-cuti/create', [IzinCutiController::class, 'create'])->name('izin-cuti.create');
        Route::post('/izin-cuti', [IzinCutiController::class, 'store'])->name('izin-cuti.store');
        Route::get('/izin-cuti/{id}/edit', [IzinCutiController::class, 'edit'])->name('izin-cuti.edit');
        Route::put('/izin-cuti/{id}', [IzinCutiController::class, 'update'])->name('izin-cuti.update');
        Route::post('/izin-cuti/{id}/approve', [IzinCutiController::class, 'approve'])->name('izin-cuti.approve');
        Route::post('/izin-cuti/{id}/reject', [IzinCutiController::class, 'reject'])->name('izin-cuti.reject');
        Route::delete('/izin-cuti/{id}', [IzinCutiController::class, 'destroy'])->name('izin-cuti.destroy');
    });
});
