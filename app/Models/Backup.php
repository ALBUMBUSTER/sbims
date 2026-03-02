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

    public function creator()
    {
        // Remove this or modify it since created_by doesn't exist
        // return $this->belongsTo(User::class, 'created_by');
        return null;
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function fileExists()
    {
        return Storage::disk('backups')->exists($this->path);
    }

    public function getFilePath()
    {
        return Storage::disk('backups')->path($this->path);
    }

    public function getFileSize()
    {
        if ($this->fileExists()) {
            return Storage::disk('backups')->size($this->path);
        }
        return 0;
    }

    public function getTableCount()
    {
        return count($this->tables_backed_up ?? []);
    }

    public function isFullBackup()
    {
        return $this->type === 'full';
    }

    public function isDatabaseBackup()
    {
        return $this->type === 'database';
    }

    public function getBackupLocation()
    {
        return storage_path('app/backups/' . $this->filename);
    }
}
