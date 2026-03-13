<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\BackupSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BackupService
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a database backup
     */
    public function createDatabaseBackup($userId = null)
    {
        try {
            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $this->backupPath . '/' . $filename;

            // Get database configuration
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE', 'sbims_pro');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            if (!empty($dbPass)) {
                $command = sprintf(
                    'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    escapeshellarg($filepath)
                );
            }

            // Execute command
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('mysqldump failed with code: ' . $returnCode);
            }

            // Get list of tables backed up
            $tables = DB::select('SHOW TABLES');
            $tablesBackedUp = array_map('current', $tables);

            // Create backup record
            $backup = Backup::create([
                'filename' => $filename,
                'path' => 'backups/' . $filename,
                'size' => filesize($filepath),
                'type' => 'database',
                'tables_backed_up' => $tablesBackedUp,
                'created_at' => now()
            ]);

            // Update last backup time in settings
            BackupSetting::set('last_backup_run', now()->toDateTimeString());
            $this->updateNextBackupTime();

            Log::info('Database backup created', [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'size' => $backup->size
            ]);

            return $backup;

        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup($userId = null)
    {
        try {
            // First create database backup
            $dbBackup = $this->createDatabaseBackup($userId);

            // Create zip archive with database and uploaded files
            $zipFilename = 'full_backup_' . date('Y-m-d_His') . '.zip';
            $zipPath = $this->backupPath . '/' . $zipFilename;

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Could not create zip archive');
            }

            // Add database backup to zip
            $zip->addFile($this->backupPath . '/' . $dbBackup->filename, 'database/' . $dbBackup->filename);

            // Add uploaded files to zip
            $uploadPath = storage_path('app/public');
            if (File::exists($uploadPath)) {
                $this->addFolderToZip($uploadPath, $zip, 'uploads');
            }

            // Add template files to zip
            $templatePath = storage_path('app/templates');
            if (File::exists($templatePath)) {
                $this->addFolderToZip($templatePath, $zip, 'templates');
            }

            $zip->close();

            // Delete the temporary database backup
            File::delete($this->backupPath . '/' . $dbBackup->filename);
            $dbBackup->delete();

            // Create full backup record
            $backup = Backup::create([
                'filename' => $zipFilename,
                'path' => 'backups/' . $zipFilename,
                'size' => filesize($zipPath),
                'type' => 'full',
                'tables_backed_up' => $dbBackup->tables_backed_up,
                'created_at' => now()
            ]);

            Log::info('Full backup created', [
                'backup_id' => $backup->id,
                'filename' => $zipFilename,
                'size' => $backup->size
            ]);

            return $backup;

        } catch (\Exception $e) {
            Log::error('Full backup failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Add folder contents to zip archive
     */
    protected function addFolderToZip($folderPath, $zip, $zipFolder)
    {
        $files = File::allFiles($folderPath);
        foreach ($files as $file) {
            $relativePath = $zipFolder . '/' . $file->getRelativePathname();
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Backup $backup)
    {
        try {
            $filepath = $this->backupPath . '/' . $backup->filename;

            if (!File::exists($filepath)) {
                throw new \Exception('Backup file not found');
            }

            if ($backup->type === 'database') {
                // Restore database from SQL file
                $this->restoreDatabase($filepath);
            } else {
                // Restore full backup
                $this->restoreFullBackup($filepath);
            }

            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'type' => $backup->type
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase($filepath)
    {
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE', 'sbims_pro');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s %s < %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($filepath)
        );

        if (!empty($dbPass)) {
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );
        }

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database restore failed');
        }
    }

    /**
     * Restore full backup
     */
    protected function restoreFullBackup($filepath)
    {
        $zip = new \ZipArchive();
        if ($zip->open($filepath) !== true) {
            throw new \Exception('Could not open backup archive');
        }

        // Extract to temporary directory
        $tempPath = storage_path('app/temp_restore_' . time());
        File::makeDirectory($tempPath, 0755, true);
        $zip->extractTo($tempPath);
        $zip->close();

        // Restore database
        $dbFile = $tempPath . '/database/' . basename($filepath, '.zip') . '.sql';
        if (File::exists($dbFile)) {
            $this->restoreDatabase($dbFile);
        }

        // Restore uploads
        if (File::exists($tempPath . '/uploads')) {
            File::copyDirectory($tempPath . '/uploads', storage_path('app/public'));
        }

        // Restore templates
        if (File::exists($tempPath . '/templates')) {
            File::copyDirectory($tempPath . '/templates', storage_path('app/templates'));
        }

        // Clean up
        File::deleteDirectory($tempPath);
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats()
    {
        try {
            $stats = [
                'total_users' => DB::table('users')->count(),
                'total_residents' => DB::table('residents')->count(),
                'total_blotters' => DB::table('blotters')->count(),
                'total_certificates' => DB::table('certificates')->count(),
                'total_logs' => DB::table('activity_logs')->count(),
                'total_size' => 0,
                'last_backup' => Backup::latest()->first()?->created_at
            ];

            // Calculate database size
            $dbName = env('DB_DATABASE', 'sbims_pro');
            $tables = DB::select('SHOW TABLE STATUS');
            foreach ($tables as $table) {
                $stats['total_size'] += $table->Data_length + $table->Index_length;
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get database stats', ['error' => $e->getMessage()]);
            return [
                'total_users' => 0,
                'total_residents' => 0,
                'total_blotters' => 0,
                'total_certificates' => 0,
                'total_logs' => 0,
                'total_size' => 0
            ];
        }
    }

    /**
     * Clean up old backups based on retention period
     */
    public function cleanupOldBackups($retentionDays)
    {
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();
        $count = 0;

        foreach ($oldBackups as $backup) {
            if ($backup->fileExists()) {
                File::delete($backup->getFilePath());
            }
            $backup->delete();
            $count++;
        }

        return $count;
    }

    /**
     * Get backup schedule settings
     */
    public function getScheduleSettings()
    {
        return [
            'schedule_type' => BackupSetting::get('schedule_type', 'daily'),
            'backup_time' => BackupSetting::get('backup_time', '02:00'),
            'retention_days' => (int)BackupSetting::get('retention_days', '30'),
            'backup_enabled' => (bool)BackupSetting::get('backup_enabled', '1'),
            'last_backup_run' => BackupSetting::get('last_backup_run'),
            'next_backup_run' => BackupSetting::get('next_backup_run')
        ];
    }

    /**
     * Update next backup time based on schedule
     */
    public function updateNextBackupTime()
    {
        $settings = $this->getScheduleSettings();

        if (!$settings['backup_enabled']) {
            BackupSetting::set('next_backup_run', null);
            return;
        }

        $now = Carbon::now();
        $time = $settings['backup_time'];

        switch ($settings['schedule_type']) {
            case 'daily':
                $next = Carbon::today()->setTimeFromTimeString($time);
                if ($next <= $now) {
                    $next->addDay();
                }
                break;

            case 'weekly':
                $next = Carbon::today()->setTimeFromTimeString($time);
                if ($next <= $now) {
                    $next->addWeek();
                }
                break;

            case 'monthly':
                $next = Carbon::today()->setTimeFromTimeString($time);
                if ($next <= $now) {
                    $next->addMonth();
                }
                break;

            default:
                $next = null;
        }

        if ($next) {
            BackupSetting::set('next_backup_run', $next->toDateTimeString());
        }
    }

    /**
     * Check if backup is due and run it
     */
    public function runScheduledBackupIfDue()
    {
        $settings = $this->getScheduleSettings();

        if (!$settings['backup_enabled']) {
            return false;
        }

        $nextRun = $settings['next_backup_run'] ? Carbon::parse($settings['next_backup_run']) : null;
        $now = Carbon::now();

        if (!$nextRun || $now >= $nextRun) {
            try {
                $this->createDatabaseBackup();
                $this->updateNextBackupTime();

                // Clean up old backups
                $this->cleanupOldBackups($settings['retention_days']);

                return true;
            } catch (\Exception $e) {
                Log::error('Scheduled backup failed', ['error' => $e->getMessage()]);
                return false;
            }
        }

        return false;
    }
}
