<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// BARIS INI YANG WAJIB ADA:
use App\Http\Controllers\API\MahasiswaController; 


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('mahasiswa', MahasiswaController::class);

