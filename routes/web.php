<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BarangayInfoController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\NotificationController; // Add this

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

        // Resident Records (Resourceful routes)
        Route::resource('residents', ResidentController::class)->except(['show']);
        Route::get('/residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
        Route::get('/residents/generate-id', [ResidentController::class, 'generateId'])->name('residents.generate-id');

        // Blotter Cases (Resourceful routes)
        Route::resource('blotter', BlotterController::class)->except(['show']);
        Route::get('/blotter/{blotter}', [BlotterController::class, 'show'])->name('blotter.show');
        Route::patch('/blotter/{blotter}/status', [BlotterController::class, 'updateStatus'])->name('blotter.status');

        // Certificates (Resourceful routes)
        Route::resource('certificates', CertificateController::class)->except(['show']);
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::post('/certificates/{certificate}/process', [CertificateController::class, 'process'])->name('certificates.process');
        Route::get('/certificates/{certificate}/print', [CertificateController::class, 'print'])->name('certificates.print');

        // Reports
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

        // System Logs
        Route::get('/logs', [SystemLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/export', [SystemLogController::class, 'export'])->name('logs.export');

        // Backups
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [BackupController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::put('/backups/schedule', [BackupController::class, 'updateSchedule'])->name('backups.schedule');
    });

    // Captain Routes
Route::prefix('captain')->name('captain.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Captain\CaptainController::class, 'dashboard'])->name('dashboard');

    // Approvals
    Route::get('/approvals', [App\Http\Controllers\Captain\CaptainController::class, 'approvals'])->name('approvals.index');
    Route::post('/approvals/certificate/{certificate}/approve', [App\Http\Controllers\Captain\CaptainController::class, 'approveCertificate'])->name('approvals.certificate.approve');
    Route::post('/approvals/certificate/{certificate}/reject', [App\Http\Controllers\Captain\CaptainController::class, 'rejectCertificate'])->name('approvals.certificate.reject');

    // Release certificate
    Route::post('/certificates/{certificate}/release', [App\Http\Controllers\Captain\CaptainController::class, 'releaseCertificate'])->name('certificates.release');

    // Residents (Read-only)
    Route::get('/residents', [App\Http\Controllers\Captain\CaptainController::class, 'residents'])->name('residents.index');
    Route::get('/residents/{resident}', [App\Http\Controllers\Captain\CaptainController::class, 'showResident'])->name('residents.show');

    // Blotters
    Route::get('/blotters', [App\Http\Controllers\Captain\CaptainController::class, 'blotters'])->name('blotters.index');
    Route::get('/blotters/{blotter}', [App\Http\Controllers\Captain\CaptainController::class, 'showBlotter'])->name('blotters.show');
    Route::post('/blotters/{blotter}/status', [App\Http\Controllers\Captain\CaptainController::class, 'updateBlotterStatus'])->name('blotters.status');

    // Report generation
    Route::post('/generate-report', [App\Http\Controllers\Captain\CaptainController::class, 'generateReport'])->name('generate-report');
});

    // Resident Routes
    Route::prefix('resident')->name('resident.')->group(function () {
        Route::get('/certificates/track/{certificate_number}', [App\Http\Controllers\Secretary\CertificateController::class, 'track'])->name('certificates.track');
    });
    Route::get('/test-notification-creation', function() {
    // Get first admin user
    $admin = App\Models\User::where('role_id', 1)->first();

    if (!$admin) {
        return 'No admin user found!';
    }

    $notification = App\Models\Notification::create([
        'user_id' => $admin->id,
        'title' => 'Test Notification',
        'message' => 'This is a test notification from ' . now(),
        'type' => 'info',
        'link' => '#',
        'is_read' => false
    ]);

    return 'Test notification created with ID: ' . $notification->id . ' for admin: ' . $admin->email;
})->middleware('auth');
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
