<?php

use App\Http\Controllers\Admin\CircleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeederController;
use App\Http\Controllers\FeederStatusLogController;
use App\Http\Controllers\Master\DivisionController;
use App\Http\Controllers\Master\FeederCategoryController;
use App\Http\Controllers\Master\FeederMasterController;
use App\Http\Controllers\Master\SubDivisionController;
use App\Http\Controllers\Master\SubstationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'scope.jurisdiction'])->group(function () {

    // Dashboard — admin & circle only
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view-dashboard');

    Route::get('/api/feeders/summary', [DashboardController::class, 'summary'])
        ->name('dashboard.summary')
        ->middleware(['permission:view-dashboard', 'throttle:60,1']);

    // Feeders list + status update — all roles
    Route::get('/feeders', [FeederController::class, 'index'])->name('feeders.index');
    Route::patch('/feeders/bulk-status', [FeederController::class, 'bulkUpdateStatus'])
        ->name('feeders.bulkUpdateStatus')
        ->middleware(['permission:update-feeder-status', 'throttle:10,1']);
    Route::patch('/feeders/{feeder}/status', [FeederController::class, 'updateStatus'])
        ->name('feeders.updateStatus')
        ->middleware(['permission:update-feeder-status', 'throttle:30,1']);

    // Status logs — admin & circle
    Route::get('/feeders/{feeder}/logs', [FeederStatusLogController::class, 'index'])
        ->name('feeders.logs')
        ->middleware('permission:view-status-logs');

    // Reports — admin & circle
    Route::get('/reports/export', [ReportController::class, 'export'])
        ->name('reports.export')
        ->middleware('permission:export-report');

    // Master data — admin & circle (own jurisdiction only)
    Route::middleware('permission:manage-division')->prefix('master')->name('master.')->group(function () {
        Route::resource('divisions', DivisionController::class)->except(['show']);
        Route::resource('sub-divisions', SubDivisionController::class)->except(['show']);
        Route::resource('substations', SubstationController::class)->except(['show']);
        Route::resource('feeders', FeederMasterController::class)->except(['show']);
        Route::resource('feeder-categories', FeederCategoryController::class)->except(['show']);
    });

    // Admin only
    Route::middleware('permission:manage-users')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('circles', CircleController::class)->except(['show']);
    });
});
