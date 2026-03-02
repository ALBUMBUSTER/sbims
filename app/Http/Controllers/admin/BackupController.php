<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        $backups = Backup::orderBy('created_at', 'desc')->get();
        $stats = $this->backupService->getDatabaseStats();

        return view('admin.backups.index', compact('backups', 'stats'));
    }

    /**
     * Create a new database backup
     */
    public function createBackup(Request $request)
{
    $request->validate([
        'type' => 'required|in:database,full'
    ]);

    try {
        if ($request->type === 'full') {
            $backup = $this->backupService->createFullBackup(Auth::id()); // Pass user ID
            $message = 'Full backup (database + files) created successfully.';
        } else {
            $backup = $this->backupService->createDatabaseBackup(Auth::id()); // Pass user ID
            $message = 'Database backup created successfully.';
        }

        Log::info('Backup created manually', [
            'backup_id' => $backup->id,
            'type' => $request->type,
            'user_id' => Auth::id()
        ]);

        return redirect()->route('admin.backups.index')
            ->with('success', $message);

    } catch (\Exception $e) {
        Log::error('Manual backup creation failed', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);

        return redirect()->route('admin.backups.index')
            ->with('error', 'Backup creation failed: ' . $e->getMessage());
    }
}

    /**
     * Download a backup file
     */
    public function download(Backup $backup)
    {
        try {
            if (!$backup->fileExists()) {
                throw new \Exception('Backup file not found');
            }

            $filepath = $backup->getFilePath();
            $filename = $backup->filename;

            Log::info('Backup downloaded', [
                'backup_id' => $backup->id,
                'user_id' => Auth::id()
            ]);

            return response()->download($filepath, $filename);

        } catch (\Exception $e) {
            Log::error('Backup download failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore from a backup
     */
    public function restore(Backup $backup)
    {
        try {
            $this->backupService->restoreBackup($backup);

            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup restored successfully. The system has been restored to the backup state.');

        } catch (\Exception $e) {
            Log::error('Backup restoration failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Restoration failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup
     */
    public function destroy(Backup $backup)
    {
        try {
            // Delete file if exists
            if ($backup->fileExists()) {
                unlink($backup->getFilePath());
            }

            // Delete record
            $backup->delete();

            Log::info('Backup deleted', [
                'backup_id' => $backup->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Backup deletion failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Update backup schedule settings
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'schedule_type' => 'required|in:daily,weekly,monthly',
            'backup_time' => 'required',
            'retention_days' => 'required|integer|min:1|max:365'
        ]);

        try {
            // Save schedule settings to a config file or database
            $scheduleData = [
                'type' => $request->schedule_type,
                'time' => $request->backup_time,
                'retention_days' => $request->retention_days,
                'updated_by' => Auth::id(),
                'updated_at' => now()
            ];

            // You might want to save this to a settings table
            // For now, we'll save to a JSON file
            $settingsPath = storage_path('app/backups/schedule.json');
            if (!file_exists(dirname($settingsPath))) {
                mkdir(dirname($settingsPath), 0755, true);
            }
            file_put_contents($settingsPath, json_encode($scheduleData, JSON_PRETTY_PRINT));

            // Clean up old backups based on new retention period
            $cleaned = $this->backupService->cleanupOldBackups($request->retention_days);

            Log::info('Backup schedule updated', $scheduleData);

            return redirect()->route('admin.backups.index')
                ->with('success', "Backup schedule updated successfully. Cleaned up {$cleaned} old backups.");

        } catch (\Exception $e) {
            Log::error('Schedule update failed', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Schedule update failed: ' . $e->getMessage());
        }
    }
}
