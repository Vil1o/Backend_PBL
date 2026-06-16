<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\External\ExternalMahasiswaController;
use App\Http\Controllers\API\Admin\AdminMahasiswaController;
use App\Http\Controllers\API\Mahasiswa\MahasiswaProfileController; 
// Import dua controller baru
use App\Http\Controllers\API\Mahasiswa\MahasiswaFamilyController;
use App\Http\Controllers\API\Mahasiswa\MahasiswaSchoolController;

// 1. Endpoint Terbuka / Antar-Service
Route::prefix('external')->group(function () {
    Route::get('/mahasiswa', [ExternalMahasiswaController::class, 'index']);
    Route::get('/mahasiswa/{nim}', [ExternalMahasiswaController::class, 'show']);
});

// 2. Endpoint Terproteksi Role Admin
Route::middleware('check.role:superadmin,admin_mahasiswa')->prefix('admin')->group(function () {
    Route::apiResource('mahasiswa', AdminMahasiswaController::class);
});

// 3. Endpoint Terproteksi Role Mahasiswa (Self-Service)
Route::middleware('check.role:mahasiswa')->prefix('mahasiswa')->group(function () {
    // Rute Profil Utama
    Route::get('/profile', [MahasiswaProfileController::class, 'showProfile']);
    Route::put('/profile', [MahasiswaProfileController::class, 'updateDetail']);

    // Rute Data Keluarga (Orang Tua / Wali)
    Route::get('/family', [MahasiswaFamilyController::class, 'showFamily']);
    Route::put('/family', [MahasiswaFamilyController::class, 'storeOrUpdateFamily']);
    Route::post('/family', [MahasiswaFamilyController::class, 'storeOrUpdateFamily']); // Tambahkan jika butuh POST

    // Rute Data Asal Sekolah
    Route::get('/school', [MahasiswaSchoolController::class, 'show']);
    Route::put('/school', [MahasiswaSchoolController::class, 'updateOrCreate']);
});