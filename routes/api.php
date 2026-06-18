<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =============================================
// KARYAWAN MOBILE API
// =============================================
Route::prefix('karyawan')->group(function () {

    // Public: Login
    Route::post('/login', [\App\Http\Controllers\Api\Karyawan\AuthController::class, 'login']);

    // Protected: Semua endpoint butuh token sanctum
    Route::middleware(['auth:sanctum', 'role:karyawan'])->group(function () {

        // Auth
        Route::post('/logout', [\App\Http\Controllers\Api\Karyawan\AuthController::class, 'logout']);

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\Karyawan\DashboardController::class, 'dashboard']);

        // Presensi
        Route::get('/presensi', [\App\Http\Controllers\Api\Karyawan\PresensiController::class, 'presensi']);
        Route::get('/rekap',    [\App\Http\Controllers\Api\Karyawan\PresensiController::class, 'rekap']);

        // Lembur
        Route::get('/lembur', [\App\Http\Controllers\Api\Karyawan\LemburController::class, 'lembur']);

        // Pengajuan Izin
        Route::get('/pengajuan-izin', [\App\Http\Controllers\Api\Karyawan\IzinController::class, 'pengajuanIzin']);

        // Profil
        Route::get('/profil',  [\App\Http\Controllers\Api\Karyawan\ProfilController::class, 'profil']);
        Route::put('/profil',  [\App\Http\Controllers\Api\Karyawan\ProfilController::class, 'updateProfil']);

        // Notifikasi
        Route::get('/notifikasi',           [\App\Http\Controllers\Api\Karyawan\NotifikasiController::class, 'notifikasi']);
        Route::post('/notifikasi/read-all', [\App\Http\Controllers\Api\Karyawan\NotifikasiController::class, 'readAllNotifikasi']);

        // Face Recognition
        Route::get('/face',         [\App\Http\Controllers\Api\FacerecognitionController::class, 'index']);
        Route::post('/face',        [\App\Http\Controllers\Api\FacerecognitionController::class, 'store']);
        Route::delete('/face',      [\App\Http\Controllers\Api\FacerecognitionController::class, 'destroyAll']);
        Route::delete('/face/{id}', [\App\Http\Controllers\Api\FacerecognitionController::class, 'destroy']);

        // Presensi / Absen
        Route::get('/presensi/info',          [\App\Http\Controllers\Api\PresensiKaryawanController::class, 'info']);
        Route::get('/presensi/jam-kerja',     [\App\Http\Controllers\Api\PresensiKaryawanController::class, 'jamKerjaList']);
        Route::post('/presensi',              [\App\Http\Controllers\Api\PresensiKaryawanController::class, 'store']);
        Route::post('/presensi/istirahat',    [\App\Http\Controllers\Api\PresensiKaryawanController::class, 'istirahat']);
    });
});

Route::apiResource('/presensimachine', App\Http\Controllers\Api\PresensiController::class);
Route::post('/presensi/log', [App\Http\Controllers\Api\PresensiController::class, 'log']);

// Endpoint fingerprint tanpa rate limiting
// Karena sudah ada mekanisme duplikasi via cache di controller
// dan mesin fingerprint perlu mengirim data real-time tanpa batasan
Route::post('/presensi/receive-data', [App\Http\Controllers\Api\PresensiController::class, 'receiveRevoData'])
    ->withoutMiddleware('throttle:api');

// Endpoint untuk capture data mentah adms
Route::any('/adms/capture', [App\Http\Controllers\Api\AdmsController::class, 'capture'])
    ->withoutMiddleware('throttle:api');

// Endpoint untuk menerima data dari mesin Fingerspot REVO melalui adms
// Route::post('/presensi/revo', [App\Http\Controllers\Api\PresensiController::class, 'receiveRevoData'])
//     ->withoutMiddleware('throttle:api');
// Endpoint untuk check versi apk terbaru
Route::get('/check-update', [App\Http\Controllers\Api\AppUpdateController::class, 'checkUpdate']);

// Update API Routes
Route::prefix('update')->group(function () {
    // Public endpoints (tidak perlu auth) - Route spesifik dulu
    Route::get('/check', [App\Http\Controllers\Api\UpdateController::class, 'checkUpdate']);
    Route::get('/version', [App\Http\Controllers\Api\UpdateController::class, 'getCurrentVersion']);
    Route::get('/list', [App\Http\Controllers\Api\UpdateController::class, 'listUpdates']);

    // Protected endpoints (disarankan menggunakan auth) - Route spesifik dulu
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/history', [App\Http\Controllers\Api\UpdateController::class, 'history']);
        Route::get('/log/{id}', [App\Http\Controllers\Api\UpdateController::class, 'showLog']);
        Route::get('/status/{logId}', [App\Http\Controllers\Api\UpdateController::class, 'getStatus']);
        Route::post('/{version}/download', [App\Http\Controllers\Api\UpdateController::class, 'downloadUpdate']);
        Route::post('/{version}/install', [App\Http\Controllers\Api\UpdateController::class, 'installUpdate']);
        Route::post('/{version}/update-now', [App\Http\Controllers\Api\UpdateController::class, 'updateNow']);
    });

    // Route dengan parameter di akhir (agar tidak conflict)
    Route::get('/{version}', [App\Http\Controllers\Api\UpdateController::class, 'show']);
});
