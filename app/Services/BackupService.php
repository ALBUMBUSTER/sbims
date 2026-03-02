<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BackupService
{
    /**
     * Create a database backup
     */
    public function createDatabaseBackup($userId = null)
    {
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $databaseName = env('DB_DATABASE');
            $tableKey = "Tables_in_{$databaseName}";

            $backupData = [];
            $tablesList = [];

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $tablesList[] = $tableName;

                // Get table data
                $rows = DB::table($tableName)->get();

                // Convert each row to array and handle special data types
                $backupData[$tableName] = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;

                    // Handle JSON columns
                    foreach ($rowArray as $key => $value) {
                        if ($this->isJsonColumn($value)) {
                            $rowArray[$key] = json_decode($value, true);
                        }
                    }

                    $backupData[$tableName][] = $rowArray;
                }
            }

            // Create backup filename
            $filename = 'backup_' . date('Y-m-d_His') . '.json';
            $path = 'backups/' . $filename;

            // Create backups directory if it doesn't exist
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            // Prepare backup data with metadata
            $backupContent = [
                'metadata' => [
                    'tables' => $tablesList,
                    'timestamp' => now()->toDateTimeString(),
                    'type' => 'database',
                    'version' => '1.0',
                    'database' => $databaseName,
                    'created_by' => $userId
                ],
                'data' => $backupData
            ];

            // Save backup data
            $jsonContent = json_encode($backupContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($jsonContent === false) {
                throw new \Exception('Failed to encode backup data to JSON');
            }

            Storage::disk('local')->put($path, $jsonContent);

            // Verify file was created and has content
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception('Backup file was not created');
            }

            $size = Storage::disk('local')->size($path);

            if ($size === 0) {
                throw new \Exception('Backup file is empty');
            }

            // Log backup content preview for debugging
            Log::info('Backup created', [
                'filename' => $filename,
                'size' => $size,
                'tables_count' => count($tablesList),
                'preview' => substr($jsonContent, 0, 500) . '...'
            ]);

            // Create backup record
            $backup = Backup::create([
                'filename' => $filename,
                'path' => $path,
                'size' => $size,
                'type' => 'database',
                'tables_backed_up' => json_encode($tablesList),
                'created_by' => $userId,
                'created_at' => now()
            ]);

            Log::info('Database backup created successfully', [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'tables' => count($tablesList),
                'size' => $this->formatBytes($size)
            ]);

            return $backup;

        } catch (\Exception $e) {
            Log::error('Failed to create database backup: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
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

            // Get database backup path
            $dbPath = Storage::disk('local')->path($dbBackup->path);

            // Create a zip file for full backup
            $filename = 'full_backup_' . date('Y-m-d_His') . '.zip';
            $path = 'backups/' . $filename;
            $fullPath = Storage::disk('local')->path($path);

            // Create zip archive
            $zip = new \ZipArchive();
            if ($zip->open($fullPath, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Failed to create zip archive');
            }

            // Add database backup to zip
            $zip->addFile($dbPath, 'database/' . $dbBackup->filename);

            // Add storage files if they exist
            $storagePaths = [
                'public' => storage_path('app/public'),
                'uploads' => storage_path('app/uploads'),
                'media' => storage_path('app/media')
            ];

            foreach ($storagePaths as $key => $dir) {
                if (is_dir($dir)) {
                    $this->addDirectoryToZip($zip, $dir, $key);
                }
            }

            $zip->close();

            // Delete the temporary database backup file
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }

            // Get zip file size
            $size = filesize($fullPath);

            // Create backup record for full backup
            $backup = Backup::create([
                'filename' => $filename,
                'path' => $path,
                'size' => $size,
                'type' => 'full',
                'tables_backed_up' => $dbBackup->tables_backed_up,
                'created_by' => $userId,
                'created_at' => now()
            ]);

            Log::info('Full backup created successfully', [
                'backup_id' => $backup->id,
                'size' => $this->formatBytes($size)
            ]);

            return $backup;

        } catch (\Exception $e) {
            Log::error('Failed to create full backup: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add directory to zip recursively
     */
    private function addDirectoryToZip($zip, $dir, $zipDir)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipDir . '/' . substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Restore from a backup
     */
    public function restoreBackup(Backup $backup)
    {
        try {
            // Read backup file
            $content = Storage::disk('local')->get($backup->path);

            if (empty($content)) {
                throw new \Exception('Backup file is empty');
            }

            $backupData = json_decode($content, true);

            if (!$backupData) {
                throw new \Exception('Invalid backup file format: ' . json_last_error_msg());
            }

            // Check if it's the new format with metadata
            if (isset($backupData['metadata']) && isset($backupData['data'])) {
                $tables = $backupData['metadata']['tables'];
                $data = $backupData['data'];
            } else {
                // Old format
                $tables = array_keys($backupData);
                $data = $backupData;
            }

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Truncate existing tables
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    Log::info('Truncated table', ['table' => $table]);
                }
            }

            // Restore data
            foreach ($data as $table => $rows) {
                if (Schema::hasTable($table) && !empty($rows)) {
                    foreach ($rows as $row) {
                        try {
                            DB::table($table)->insert($row);
                        } catch (\Exception $e) {
                            Log::warning('Failed to insert row', [
                                'table' => $table,
                                'error' => $e->getMessage(),
                                'row' => json_encode($row)
                            ]);
                        }
                    }
                    Log::info('Restored table', [
                        'table' => $table,
                        'rows' => count($rows)
                    ]);
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Log::info('Backup restored successfully', [
                'backup_id' => $backup->id,
                'tables' => count($tables)
            ]);

            return true;

        } catch (\Exception $e) {
            // Re-enable foreign key checks even if there's an error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Log::error('Failed to restore backup: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Clean up old backups based on retention days
     */
    public function cleanupOldBackups($retentionDays)
    {
        try {
            $cutoffDate = now()->subDays($retentionDays);

            // Get old backups
            $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();
            $count = 0;

            foreach ($oldBackups as $backup) {
                // Delete file
                if (Storage::disk('local')->exists($backup->path)) {
                    Storage::disk('local')->delete($backup->path);
                    Log::info('Deleted backup file', ['path' => $backup->path]);
                }
                // Delete record
                $backup->delete();
                $count++;
            }

            Log::info('Old backups cleaned up', [
                'count' => $count,
                'retention_days' => $retentionDays
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to cleanup old backups: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats()
    {
        try {
            // Get database size
            $databaseName = env('DB_DATABASE');
            $result = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$databaseName]);

            $size = isset($result[0]) ? $result[0]->size_mb * 1048576 : 0;

            // Get table counts
            $stats = [
                'database_size' => $size,
                'total_users' => DB::table('users')->count(),
                'total_residents' => DB::table('residents')->count(),
                'total_blotters' => DB::table('blotters')->count(),
                'total_certificates' => DB::table('certificates')->count(),
                'total_logs' => DB::table('activity_logs')->count(),
            ];

            // Get last backup
            $lastBackup = Backup::latest('created_at')->first();
            if ($lastBackup) {
                $stats['last_backup'] = $lastBackup->created_at;
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get database stats: ' . $e->getMessage());
            return [
                'database_size' => 0,
                'total_users' => 0,
                'total_residents' => 0,
                'total_blotters' => 0,
                'total_certificates' => 0,
                'total_logs' => 0,
            ];
        }
    }

    /**
     * Check if a value is a JSON column
     */
    private function isJsonColumn($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $trimmed = trim($value);
        if (empty($trimmed)) {
            return false;
        }

        if (($trimmed[0] === '{' && substr($trimmed, -1) === '}') ||
            ($trimmed[0] === '[' && substr($trimmed, -1) === ']')) {
            json_decode($trimmed);
            return json_last_error() === JSON_ERROR_NONE;
        }

        return false;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
