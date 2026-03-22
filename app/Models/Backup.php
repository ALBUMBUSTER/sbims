<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'path',
        'size',
        'type',
        'tables_backed_up',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'size' => 'integer',
        'tables_backed_up' => 'array'
    ];

    // Disable updated_at since your table doesn't have it
    const UPDATED_AT = null;

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if backup file exists
     */
    public function fileExists()
    {
        $fullPath = $this->getFilePath();
        return file_exists($fullPath);
    }

    /**
     * Get full path to backup file
     */
    public function getFilePath()
    {
        // The path is stored as 'backups/filename.sql' in database
        // But the actual file is in storage_path('app/backups/filename.sql')
        return storage_path('app/' . $this->path);
    }

    /**
     * Get file size
     */
    public function getFileSize()
    {
        if ($this->fileExists()) {
            return filesize($this->getFilePath());
        }
        return 0;
    }

    /**
     * Get table count
     */
    public function getTableCount()
    {
        return count($this->tables_backed_up ?? []);
    }

    /**
     * Check if full backup
     */
    public function isFullBackup()
    {
        return $this->type === 'full';
    }

    /**
     * Check if database backup
     */
    public function isDatabaseBackup()
    {
        return $this->type === 'database';
    }

    /**
     * Get backup location (alias for getFilePath)
     */
    public function getBackupLocation()
    {
        return $this->getFilePath();
    }
}
