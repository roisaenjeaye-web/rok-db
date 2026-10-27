<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\KingdomController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GameAccountController;

Route::get('/', function () {
    return redirect('/stok');
});

// Semua halaman operasional joki diwajibkan login agar otomatis mengenali operator
Route::middleware('auth')->group(function () {
    // Fitur CRUD Stok Kingdom
    Route::get('/stok', [KingdomController::class, 'index'])->name('home');
    Route::post('/stok/tambah', [KingdomController::class, 'store']);
    Route::get('/stok/{id}/edit', [KingdomController::class, 'edit']);
    Route::post('/stok/{id}/update', [KingdomController::class, 'update']);
    Route::post('/stok/{id}/hapus', [KingdomController::class, 'destroy']);

    // Fitur Manajemen Gaji Joki (Input Manual, Edit, Hapus)
    Route::post('/gaji/simpan', [KingdomController::class, 'storeSalary']);
    Route::post('/gaji/{id}/update', [KingdomController::class, 'updateSalary']);
    Route::post('/gaji/{id}/hapus', [KingdomController::class, 'destroySalary']);

    // Fitur Kalkulator Joki & Order
    Route::get('/kalkulator', [CalculatorController::class, 'index']);
    Route::post('/kalkulator/hitung', [CalculatorController::class, 'calculate']);
    Route::post('/kalkulator/simpan', [OrderController::class, 'store']);
    Route::get('/riwayat', [OrderController::class, 'index']);
    Route::post('/riwayat/{id}/selesai', [OrderController::class, 'complete']);

    // Fitur Manajemen Akun & Ternak Farm
    Route::get('/akun', [GameAccountController::class, 'index']);
    Route::post('/akun/tambah', [GameAccountController::class, 'storeAccount']);
    Route::post('/akun/tambah-char', [GameAccountController::class, 'storeCharacter']);
    Route::post('/akun/char/{id}/farm', [GameAccountController::class, 'farmDone']);
    Route::post('/akun/char-bulk/farm', [GameAccountController::class, 'farmDoneBulk']);
    Route::post('/akun/char/{id}/adjust-log', [GameAccountController::class, 'adjustCharLog']);

    // Fitur Jadwal Farm & Panen Serentak
    Route::get('/jadwal', [GameAccountController::class, 'jadwal']);
    Route::post('/jadwal/kd/{kd}/farm', [GameAccountController::class, 'farmDoneSuperBulk']);
    Route::post('/jadwal/kd/{kd}/adjust-log', [GameAccountController::class, 'adjustKdLog']);
});