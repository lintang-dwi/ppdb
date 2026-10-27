<?php

use App\Http\Controllers\Cms\ApplicantActionToggleController;
use App\Http\Controllers\Cms\ApplicantExportController;
use App\Http\Controllers\Cms\ApplicantTableController;
use App\Http\Controllers\Cms\AuthController;
use App\Http\Controllers\Cms\DashboardOverviewController;
use App\Http\Controllers\Cms\FormBuilderController;
use App\Http\Controllers\Cms\GoogleDriveSettingController;
use App\Http\Controllers\Cms\SettingPeriodController;
use App\Http\Controllers\Cms\TrackManagementController;
use App\Http\Controllers\Cms\WalkInDeskController;
use App\Http\Controllers\Public\ApplicantSlipController;
use App\Http\Controllers\Public\ApplicantStatusController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\RegistrationWizardController;
use App\Http\Middleware\EnsureRegistrationPeriodOpen;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes (Shared across Custom Domain and Sub-Path Prefix)
|--------------------------------------------------------------------------
*/

// Public Landing Page
Route::get('/', function (TenantContextService $tenant) {
    if (! $tenant->hasTenant()) {
        if (request()->wantsJson()) {
            return response()->json([
                'engine' => 'PPDB Multi-Tenant Monolith',
                'status' => 'operational',
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return view('pages.public.central_landing');
    }

    return app(LandingController::class)();
})->name('tenant.home');

// P5: Public Registration Wizard (Protected by period open check)
Route::middleware(EnsureRegistrationPeriodOpen::class)->group(function () {
    Route::get('/daftar', [RegistrationWizardController::class, 'index'])->name('applicant.register.index');
    Route::post('/daftar', [RegistrationWizardController::class, 'store'])->name('applicant.register.store');
});
Route::get('/daftar/selesai', [RegistrationWizardController::class, 'success'])->name('applicant.register.success');

// Safety Slip and Status Portal
Route::get('/cek-status', [ApplicantStatusController::class, 'lookup'])->name('applicant.status.lookup');
Route::post('/cek-status', [ApplicantStatusController::class, 'check'])->name('applicant.status.check');
Route::get('/cek-status/{registration_number}', [ApplicantStatusController::class, 'show'])->name('applicant.status.show');
Route::get('/cek-status/{registration_number}/perbaiki', [ApplicantStatusController::class, 'reuploadForm'])->name('applicant.status.reupload');
Route::post('/cek-status/{registration_number}/perbaiki', [ApplicantStatusController::class, 'submitRevision'])->name('applicant.status.submit-revision');

Route::get('/bukti-pendaftaran', ApplicantSlipController::class)->name('applicant.slip.download');

// CMS Guest Auth Routes
Route::prefix('cms')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('cms.login');
    Route::post('/login', [AuthController::class, 'login'])->name('cms.login.submit');
});

// CMS Protected Routes (Scoped to current school + auth:ppdb)
Route::prefix('cms')->middleware(['auth:ppdb', 'staff.tenant'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('cms.logout');
    Route::get('/dashboard', DashboardOverviewController::class)->name('cms.dashboard');
    Route::get('/', fn () => redirect()->to(tenant_route('cms.dashboard')));

    // P4a: Gelombang Pendaftaran
    Route::get('/waves', [SettingPeriodController::class, 'index'])->name('cms.waves.index');
    Route::post('/waves', [SettingPeriodController::class, 'store'])->name('cms.waves.store');
    Route::put('/waves/{wave}', [SettingPeriodController::class, 'update'])->name('cms.waves.update');
    Route::post('/waves/{wave}/toggle', [SettingPeriodController::class, 'toggle'])->name('cms.waves.toggle');

    // P4b: Jalur Masuk & Kuota
    Route::get('/tracks', [TrackManagementController::class, 'index'])->name('cms.tracks.index');
    Route::post('/tracks', [TrackManagementController::class, 'store'])->name('cms.tracks.store');
    Route::put('/tracks/{track}', [TrackManagementController::class, 'update'])->name('cms.tracks.update');
    Route::post('/tracks/{track}/toggle', [TrackManagementController::class, 'toggle'])->name('cms.tracks.toggle');
    Route::post('/tracks/reorder', [TrackManagementController::class, 'reorder'])->name('cms.tracks.reorder');

    // P4c: Form Builder
    Route::get('/form-builder', [FormBuilderController::class, 'index'])->name('cms.form-builder.index');
    Route::post('/form-builder', [FormBuilderController::class, 'store'])->name('cms.form-builder.store');
    Route::put('/form-builder/{field}', [FormBuilderController::class, 'update'])->name('cms.form-builder.update');
    Route::delete('/form-builder/{field}', [FormBuilderController::class, 'destroy'])->name('cms.form-builder.destroy');
    Route::post('/form-builder/{field}/toggle', [FormBuilderController::class, 'toggle'])->name('cms.form-builder.toggle');
    Route::post('/form-builder/apply-preset', [FormBuilderController::class, 'applyPreset'])->name('cms.form-builder.apply-preset');

    // P9: Applicant Table, Toggles, and Export
    Route::get('/applicants/export', ApplicantExportController::class)->name('cms.applicants.export');
    Route::get('/applicants', [ApplicantTableController::class, 'index'])->name('cms.applicants.index');
    Route::get('/applicants/{applicant}', [ApplicantTableController::class, 'show'])->name('cms.applicants.show');
    Route::patch('/applicants/{applicant}/status', [ApplicantActionToggleController::class, 'updateStatus'])->name('cms.applicants.update-status');
    Route::patch('/documents/{document}/verify', [ApplicantActionToggleController::class, 'verifyDocument'])->name('cms.documents.verify');
    Route::patch('/documents/{document}/physical', [ApplicantActionToggleController::class, 'togglePhysicalDoc'])->name('cms.documents.physical');

    // P10: Walk-In Desk (Loket Offline)
    Route::get('/walk-in', [WalkInDeskController::class, 'index'])->name('cms.walk-in.index');
    Route::post('/walk-in', [WalkInDeskController::class, 'store'])->name('cms.walk-in.store');

    // Google Drive Tenant Storage Settings
    Route::get('/google-drive', [GoogleDriveSettingController::class, 'index'])->name('cms.gdrive.index');
    Route::get('/google-drive/connect', [GoogleDriveSettingController::class, 'connect'])->name('cms.gdrive.connect');
    Route::get('/google-drive/callback', [GoogleDriveSettingController::class, 'callback'])->name('cms.gdrive.callback');
    Route::delete('/google-drive/disconnect', [GoogleDriveSettingController::class, 'disconnect'])->name('cms.gdrive.disconnect');
});
