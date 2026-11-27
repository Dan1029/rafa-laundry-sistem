<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\SlotPickupController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::resource('layanan', LayananController::class)->except('show');
        Route::resource('slot-pickups', SlotPickupController::class)->except('show');
        Route::resource('pengguna', PenggunaController::class)->except('show');
    });

    Route::middleware('role:admin,kasir')->group(function () {
        Route::resource('pesanans', PesananController::class)->except('show');
    });
});
