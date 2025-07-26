<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;

Route::get('/', function () {
    return view('absen'); // view Blade seperti absen.blade.php
});

// ✅ Route untuk menerima data absensi dari frontend
Route::post('/absensi', [AbsensiController::class, 'store']);
