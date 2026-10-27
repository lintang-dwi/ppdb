<?php

use App\Http\Controllers\Superadmin\SuperadminAuthController;
use App\Http\Controllers\Superadmin\SuperadminDashboardController;
use App\Http\Middleware\IdentifyTenantPpdb;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Superadmin Platform Routes
Route::redirect('/admin', '/superadmin');
Route::prefix('superadmin')->as('superadmin.')->group(function () {
    Route::get('/login', [SuperadminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SuperadminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [SuperadminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:superadmin')->group(function () {
        Route::get('/', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::put('/schools/{school:school_code}', [SuperadminDashboardController::class, 'updateSchool'])->name('schools.update');
        Route::post('/impersonate/{school:school_code}', [SuperadminDashboardController::class, 'impersonate'])->name('impersonate');
        Route::get('/leave-impersonate', [SuperadminDashboardController::class, 'leaveImpersonate'])->name('leave-impersonate');
    });
});

// Public Legal & Information Pages
Route::view('/privacy-policy', 'pages.public.privacy_policy')->name('privacy');
Route::view('/terms', 'pages.public.terms')->name('terms');

// Mode 1: Sub-path Prefix (e.g., ppdb.bitserver.cloud/{school_code}/*)
Route::prefix('{school_code}')
    ->middleware([IdentifyTenantPpdb::class])
    ->as('tenant.prefix.')
    ->group(base_path('routes/tenant.php'));

// Mode 2: Custom Domain CNAME (e.g., ppdb.sekolah.sch.id/*) & Central Root
Route::middleware([IdentifyTenantPpdb::class])
    ->as('tenant.custom.')
    ->group(base_path('routes/tenant.php'));
