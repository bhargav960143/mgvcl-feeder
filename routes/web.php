<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeederController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected
Route::middleware(['auth', 'scope.jurisdiction'])->group(function () {

    // Dashboard — admin & circle only
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view-dashboard');

    Route::get('/api/feeders/summary', [DashboardController::class, 'summary'])
        ->name('dashboard.summary')
        ->middleware(['permission:view-dashboard', 'throttle:60,1']);

    // Feeders — all roles
    Route::get('/feeders', [FeederController::class, 'index'])->name('feeders.index');

    Route::patch('/feeders/{feeder}/status', [FeederController::class, 'updateStatus'])
        ->name('feeders.updateStatus')
        ->middleware('throttle:30,1');
});
