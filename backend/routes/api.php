<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\InventarisController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\KerusakanController;
use App\Http\Controllers\Api\InsidenController;
use App\Http\Controllers\Api\ModulController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\MatkulController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\RuanganController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\NilaiController;
use App\Http\Controllers\Api\PemeliharaanController;
use App\Http\Controllers\Api\BookingRuangController;
use App\Http\Controllers\Api\LaporanController;

// ── Public routes ──────────────────────────────────────────────────────────
Route::post('/login',       [AuthController::class, 'login']);
Route::post('/register',    [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// ── Protected routes ───────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Profil
    Route::get('/profil',           [ProfilController::class, 'show']);
    Route::put('/profil',           [ProfilController::class, 'update']);
    Route::post('/profil/foto',     [ProfilController::class, 'uploadFoto']);
    Route::put('/profil/password',  [ProfilController::class, 'changePassword']);

    // Manajemen pengguna (admin)
    Route::apiResource('users', UserController::class);
    Route::post('/users/{id}/toggle', [UserController::class, 'toggle']);

    // Master data
    Route::apiResource('inventaris', InventarisController::class);
    Route::apiResource('matkul', MatkulController::class)->except(['show']);
    Route::apiResource('jadwal', JadwalController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('ruangan', RuanganController::class)->except(['show']);
    Route::post('/ruangan/{id}/toggle', [RuanganController::class, 'toggle']);

    // Peminjaman
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::post('/peminjaman/{id}/setujui',    [PeminjamanController::class, 'setujui']);
    Route::post('/peminjaman/{id}/tolak',      [PeminjamanController::class, 'tolak']);
    Route::post('/peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan']);

    // Booking ruang (fitur baru)
    Route::apiResource('booking-ruang', BookingRuangController::class);
    Route::post('/booking-ruang/{id}/setujui', [BookingRuangController::class, 'setujui']);
    Route::post('/booking-ruang/{id}/tolak',   [BookingRuangController::class, 'tolak']);

    // Kerusakan & insiden
    Route::apiResource('kerusakan', KerusakanController::class);
    Route::post('/kerusakan/{id}/tangani', [KerusakanController::class, 'tangani']);
    Route::apiResource('insiden', InsidenController::class);
    Route::post('/insiden/{id}/tangani',   [InsidenController::class, 'tangani']);

    // Modul
    Route::apiResource('modul', ModulController::class);
    Route::get('/modul/{id}/unduh', [ModulController::class, 'unduh']);

    // Akademik
    Route::apiResource('absensi',      AbsensiController::class)->only(['index', 'store', 'update']);
    Route::apiResource('nilai',        NilaiController::class)->only(['index', 'store', 'update']);
    Route::apiResource('pemeliharaan', PemeliharaanController::class)->only(['index', 'store']);
    Route::post('/pemeliharaan/{id}/selesai', [PemeliharaanController::class, 'selesai']);

    // Laporan (fitur baru)
    Route::get('/laporan', [LaporanController::class, 'index']);

    // Notifikasi
    Route::get('/notifikasi',                    [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/unread-count',       [NotifikasiController::class, 'unreadCount']);
    Route::post('/notifikasi/baca-semua',        [NotifikasiController::class, 'tandaiSemua']);
    Route::post('/notifikasi/{id}/baca',         [NotifikasiController::class, 'tandaiBaca']);
});
