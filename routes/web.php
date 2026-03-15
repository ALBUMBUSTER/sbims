<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BarangayInfoController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\NotificationController;

// Secretary Controllers
use App\Http\Controllers\Secretary\ResidentController;
use App\Http\Controllers\Secretary\BlotterController;
use App\Http\Controllers\Secretary\CertificateController;
use App\Http\Controllers\Secretary\ReportController;

// Authentication Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard Routes
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/captain/dashboard', [DashboardController::class, 'captain'])->name('captain.dashboard');
    Route::get('/secretary/dashboard', [DashboardController::class, 'secretary'])->name('secretary.dashboard');
    Route::get('/resident/dashboard', [DashboardController::class, 'resident'])->name('resident.dashboard');

    // Role-based dashboard redirect
    Route::get('/dashboard', [DashboardController::class, 'redirectToRoleDashboard'])->name('dashboard');

    // Secretary Routes
    Route::prefix('secretary')->name('secretary.')->group(function () {
    // Dashboard & Activities
    Route::get('/dashboard', [DashboardController::class, 'secretary'])->name('dashboard');
    Route::get('/activities', [DashboardController::class, 'activities'])->name('activities');

    // ===== RESIDENTS =====
    // IMPORT ROUTES - Place these BEFORE resource routes
    Route::get('/residents/import', [ResidentController::class, 'showImportForm'])->name('residents.import');
    Route::post('/residents/import/upload', [ResidentController::class, 'uploadImport'])->name('residents.import.upload');
    Route::post('/residents/import/map', [ResidentController::class, 'processMapping'])->name('residents.import.map');
    Route::post('/residents/import/confirm', [ResidentController::class, 'confirmImport'])->name('residents.import.confirm');
    Route::get('/residents/import/template', [ResidentController::class, 'downloadTemplate'])->name('residents.import.template');

    // ARCHIVE ROUTES - Place these BEFORE resource routes
    Route::post('/residents/{resident}/archive', [ResidentController::class, 'archive'])->name('residents.archive');
    Route::get('/residents/archived', [ResidentController::class, 'archived'])->name('residents.archived');
    Route::post('/residents/{id}/restore', [ResidentController::class, 'restore'])->name('residents.restore');
    Route::delete('/residents/{id}/force-delete', [ResidentController::class, 'forceDelete'])->name('residents.force-delete');

    // Resident Records (Resourceful routes)
    Route::resource('residents', ResidentController::class)->except(['show']);
    Route::get('/residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
    Route::get('/residents/generate-id', [ResidentController::class, 'generateId'])->name('residents.generate-id');
    Route::get('/residents/generate-pwd-id', [ResidentController::class, 'generatePwdId'])->name('residents.generate-pwd-id');

    // ===== BLOTTER =====
    // Blotter Archive Routes - Place these BEFORE resource routes
    Route::post('/blotter/{blotter}/archive', [BlotterController::class, 'archive'])->name('blotter.archive');
    Route::get('/blotter/archived', [BlotterController::class, 'archived'])->name('blotter.archived');
    Route::post('/blotter/{id}/restore', [BlotterController::class, 'restore'])->name('blotter.restore');
    Route::delete('/blotter/{id}/force-delete', [BlotterController::class, 'forceDelete'])->name('blotter.force-delete');

    // Blotter Cases (Resourceful routes)
    Route::resource('blotter', BlotterController::class)->except(['show']);
    Route::get('/blotter/{blotter}', [BlotterController::class, 'show'])->name('blotter.show');
    Route::patch('/blotter/{blotter}/status', [BlotterController::class, 'updateStatus'])->name('blotter.status');

    // ===== CERTIFICATES =====
    // Certificate Archive Routes - Place these BEFORE resource routes
    Route::post('/certificates/{certificate}/archive', [CertificateController::class, 'archive'])->name('certificates.archive');
    Route::get('/certificates/archived', [CertificateController::class, 'archived'])->name('certificates.archived');
    Route::post('/certificates/{id}/restore', [CertificateController::class, 'restore'])->name('certificates.restore');
    Route::delete('/certificates/{id}/force-delete', [CertificateController::class, 'forceDelete'])->name('certificates.force-delete');

    // Certificates (Resourceful routes)
    Route::resource('certificates', CertificateController::class)->except(['show']);
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::post('/certificates/{certificate}/process', [CertificateController::class, 'process'])->name('certificates.process');
    Route::get('/certificates/{certificate}/print', [CertificateController::class, 'print'])->name('certificates.print');
    Route::get('/certificates/{certificate}/generate-doc', [App\Http\Controllers\TemplateController::class, 'generateCertificate'])->name('certificates.generate-doc');
    Route::get('/certificates/{certificate}/print-doc', [App\Http\Controllers\TemplateController::class, 'printCertificate'])->name('certificates.print-doc');

    // ===== REPORTS =====
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/residents', [ReportController::class, 'residents'])->name('residents');
        Route::get('/certificates', [ReportController::class, 'certificates'])->name('certificates');
        Route::get('/blotter', [ReportController::class, 'blotter'])->name('blotter');
        Route::get('/summary', [ReportController::class, 'summary'])->name('summary');
        Route::post('/export', [ReportController::class, 'export'])->name('export');
    });
});
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // User Management (Resourceful routes)
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Barangay Information
        Route::get('/barangay', [BarangayInfoController::class, 'index'])->name('barangay.index');
        Route::put('/barangay', [BarangayInfoController::class, 'update'])->name('barangay.update');

        // SYSTEM LOGS - ORDER IS CRITICAL!
        // 1. First, define all specific routes (without parameters)
        Route::get('/logs/export', [SystemLogController::class, 'export'])->name('logs.export');
        Route::delete('/logs/bulk-delete', [SystemLogController::class, 'bulkDestroy'])->name('logs.bulk-delete');
        Route::get('/logs/refresh-stats', [SystemLogController::class, 'refreshStats'])->name('logs.refresh-stats');

        // 2. Then define the index route
        Route::get('/logs', [SystemLogController::class, 'index'])->name('logs.index');

        // 3. LASTLY, define the parameterized route (with {log} parameter)
        Route::delete('/logs/{log}', [SystemLogController::class, 'destroy'])->name('logs.destroy');

        // Backups
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [BackupController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::put('/backups/schedule', [BackupController::class, 'updateSchedule'])->name('backups.schedule');
        Route::put('/barangay/update-stats', [BarangayInfoController::class, 'updateStats'])->name('barangay.update-stats');
    });

    // Captain Routes
    Route::prefix('captain')->name('captain.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Captain\CaptainController::class, 'dashboard'])->name('dashboard');

        // Residents - Using Secretary ResidentController with Captain views
        Route::get('/residents', [App\Http\Controllers\Secretary\ResidentController::class, 'index'])->name('residents.index');
        Route::get('/residents/create', [App\Http\Controllers\Secretary\ResidentController::class, 'create'])->name('residents.create');
        Route::post('/residents', [App\Http\Controllers\Secretary\ResidentController::class, 'store'])->name('residents.store');
        Route::get('/residents/{resident}', [App\Http\Controllers\Secretary\ResidentController::class, 'show'])->name('residents.show');
        Route::get('/residents/{resident}/edit', [App\Http\Controllers\Secretary\ResidentController::class, 'edit'])->name('residents.edit');
        Route::put('/residents/{resident}', [App\Http\Controllers\Secretary\ResidentController::class, 'update'])->name('residents.update');
        Route::delete('/residents/{resident}', [App\Http\Controllers\Secretary\ResidentController::class, 'destroy'])->name('residents.destroy');
        Route::get('/residents/import', [App\Http\Controllers\Secretary\ResidentController::class, 'showImportForm'])->name('residents.import');
        Route::post('/residents/import', [App\Http\Controllers\Secretary\ResidentController::class, 'import'])->name('residents.import.post');
        Route::get('/residents/generate-id', [App\Http\Controllers\Secretary\ResidentController::class, 'generateId'])->name('residents.generate-id');

        // Blotters - Using Secretary BlotterController with Captain views
        Route::get('/blotters', [App\Http\Controllers\Secretary\BlotterController::class, 'index'])->name('blotters.index');
        Route::get('/blotters/create', [App\Http\Controllers\Secretary\BlotterController::class, 'create'])->name('blotters.create');
        Route::post('/blotters', [App\Http\Controllers\Secretary\BlotterController::class, 'store'])->name('blotters.store');
        Route::get('/blotters/{blotter}', [App\Http\Controllers\Secretary\BlotterController::class, 'show'])->name('blotters.show');
        Route::get('/blotters/{blotter}/edit', [App\Http\Controllers\Secretary\BlotterController::class, 'edit'])->name('blotters.edit');
        Route::put('/blotters/{blotter}', [App\Http\Controllers\Secretary\BlotterController::class, 'update'])->name('blotters.update');
        Route::delete('/blotters/{blotter}', [App\Http\Controllers\Secretary\BlotterController::class, 'destroy'])->name('blotters.destroy');
        Route::patch('/blotters/{blotter}/status', [App\Http\Controllers\Secretary\BlotterController::class, 'updateStatus'])->name('blotters.status');

        // Certificates - Using Secretary CertificateController
        Route::get('/certificates', [App\Http\Controllers\Secretary\CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/create', [App\Http\Controllers\Secretary\CertificateController::class, 'create'])->name('certificates.create');
        Route::post('/certificates', [App\Http\Controllers\Secretary\CertificateController::class, 'store'])->name('certificates.store');
        Route::get('/certificates/{certificate}', [App\Http\Controllers\Secretary\CertificateController::class, 'show'])->name('certificates.show');
        Route::get('/certificates/{certificate}/edit', [App\Http\Controllers\Secretary\CertificateController::class, 'edit'])->name('certificates.edit');
        Route::put('/certificates/{certificate}', [App\Http\Controllers\Secretary\CertificateController::class, 'update'])->name('certificates.update');
        Route::delete('/certificates/{certificate}', [App\Http\Controllers\Secretary\CertificateController::class, 'destroy'])->name('certificates.destroy');
        Route::post('/certificates/{certificate}/process', [App\Http\Controllers\Secretary\CertificateController::class, 'process'])->name('certificates.process');
        Route::get('/certificates/{certificate}/print', [App\Http\Controllers\Secretary\CertificateController::class, 'print'])->name('certificates.print');

        // Reports - Using Secretary ReportController
        Route::get('/reports', [App\Http\Controllers\Secretary\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/residents', [App\Http\Controllers\Secretary\ReportController::class, 'residents'])->name('reports.residents');
        Route::get('/reports/certificates', [App\Http\Controllers\Secretary\ReportController::class, 'certificates'])->name('reports.certificates');
        Route::get('/reports/blotter', [App\Http\Controllers\Secretary\ReportController::class, 'blotter'])->name('reports.blotter');
        Route::get('/reports/summary', [App\Http\Controllers\Secretary\ReportController::class, 'summary'])->name('reports.summary');
        Route::post('/reports/export', [App\Http\Controllers\Secretary\ReportController::class, 'export'])->name('reports.export');

        // Approvals (Captain-specific)
        Route::get('/approvals', [App\Http\Controllers\Captain\CaptainController::class, 'approvals'])->name('approvals.index');
        Route::post('/approvals/certificate/{certificate}/approve', [App\Http\Controllers\Captain\CaptainController::class, 'approveCertificate'])->name('approvals.certificate.approve');
        Route::post('/approvals/certificate/{certificate}/reject', [App\Http\Controllers\Captain\CaptainController::class, 'rejectCertificate'])->name('approvals.certificate.reject');
        Route::post('/certificates/{certificate}/release', [App\Http\Controllers\Captain\CaptainController::class, 'releaseCertificate'])->name('certificates.release');
        Route::post('/generate-report', [App\Http\Controllers\Captain\CaptainController::class, 'generateReport'])->name('generate-report');

        // Inside captain group
        Route::get('/certificates/{certificate}/print-doc', [App\Http\Controllers\TemplateController::class, 'printCertificate'])
        ->name('certificates.print-doc');
    });

    // ============= CLERK ROUTES =============
    Route::prefix('clerk')->name('clerk.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Clerk\ClerkController::class, 'dashboard'])->name('dashboard');

        // Residents (Read-only) - Using Secretary ResidentController but with Clerk views
        Route::get('/residents', [App\Http\Controllers\Secretary\ResidentController::class, 'index'])->name('residents.index');
        Route::get('/residents/{resident}', [App\Http\Controllers\Secretary\ResidentController::class, 'show'])->name('residents.show');

        // IMPORTANT: Do NOT include edit, update, or destroy routes for Clerk

        // Certificates (Create and View only)
        Route::get('/certificates', [App\Http\Controllers\Secretary\CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/create', [App\Http\Controllers\Secretary\CertificateController::class, 'create'])->name('certificates.create');
        Route::post('/certificates', [App\Http\Controllers\Secretary\CertificateController::class, 'store'])->name('certificates.store');
        Route::get('/certificates/{certificate}', [App\Http\Controllers\Secretary\CertificateController::class, 'show'])->name('certificates.show');
        Route::get('/certificates/{certificate}/print', [App\Http\Controllers\Secretary\CertificateController::class, 'print'])->name('certificates.print');

        // Do NOT include edit, update, or process routes for Clerk

        // Reports (Read-only)
        Route::get('/reports', [App\Http\Controllers\Secretary\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/residents', [App\Http\Controllers\Secretary\ReportController::class, 'residents'])->name('reports.residents');
        Route::get('/reports/certificates', [App\Http\Controllers\Secretary\ReportController::class, 'certificates'])->name('reports.certificates');
        Route::get('/reports/summary', [App\Http\Controllers\Secretary\ReportController::class, 'summary'])->name('reports.summary');
    });

    // Resident Routes
    Route::prefix('resident')->name('resident.')->group(function () {
        Route::get('/certificates/track/{certificate_number}', [App\Http\Controllers\Secretary\CertificateController::class, 'track'])->name('certificates.track');
    });
});
// Password Recovery Routes (public)
Route::prefix('password')->name('password.')->group(function () {
    Route::get('/forgot', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showForm'])->name('request');
    Route::post('/recover/step1', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'step1'])->name('recover.step1');
    Route::get('/recover/step2', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showStep2'])->name('recover.step2');
    Route::post('/recover/step2', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'step2'])->name('recover.step2');
    Route::get('/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('reset.form');
    Route::post('/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('reset');
    Route::get('/cancel', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'cancel'])->name('cancel');
});
// Username Recovery Routes
Route::prefix('username')->name('username.')->group(function () {
    Route::get('/recover', [App\Http\Controllers\Auth\UsernameRecoveryController::class, 'showRecoveryForm'])->name('recover');
    Route::post('/recover/find', [App\Http\Controllers\Auth\UsernameRecoveryController::class, 'findUser'])->name('recover.find');
    Route::get('/recover/question', [App\Http\Controllers\Auth\UsernameRecoveryController::class, 'showQuestion'])->name('recover.question');
    Route::post('/recover/verify', [App\Http\Controllers\Auth\UsernameRecoveryController::class, 'verifyAnswer'])->name('recover.verify');
    Route::get('/recover/cancel', [App\Http\Controllers\Auth\UsernameRecoveryController::class, 'cancel'])->name('recover.cancel');
});

// ========== GLOBAL NOTIFICATION ROUTES (accessible by all authenticated users) ==========
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

});
