<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SyncBackupsToDatabase extends Command
{
    protected $signature = 'backups:sync';
    protected $description = 'Sync existing backup files to database';

    public function handle()
    {
        $this->info('Starting backup sync...');

        // Get all files in the backups directory
        $files = Storage::disk('backups')->files();

        if (empty($files)) {
            $this->warn('No backup files found in storage/app/backups/');
            return 0;
        }

        $this->info('Found ' . count($files) . ' files in backups directory');

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($files as $file) {
            // Skip non-backup files
            if ($file === 'schedule.json') {
                $this->line("⏭️  Skipping {$file} - not a backup file");
                $skipped++;
                continue;
            }

            try {
                // Check if backup already exists in database
                $exists = Backup::where('filename', $file)->exists();

                if ($exists) {
                    $this->line("⏭️  Skipping {$file} - already in database");
                    $skipped++;
                    continue;
                }

                // Get file info
                $size = Storage::disk('backups')->size($file);

                // Determine type based on filename
                $type = str_contains($file, 'full_backup') ? 'full' : 'database';

                // Try to extract tables from the backup file if it's a JSON backup
                $tablesBackedUp = null;
                if ($type === 'database' && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    try {
                        $content = Storage::disk('backups')->get($file);
                        $data = json_decode($content, true);

                        if ($data && isset($data['metadata']['tables'])) {
                            $tablesBackedUp = json_encode($data['metadata']['tables']);
                        } elseif ($data && isset($data['tables'])) {
                            $tablesBackedUp = json_encode($data['tables']);
                        } elseif ($data && isset($data['data'])) {
                            // New format with data key
                            $tablesBackedUp = json_encode(array_keys($data['data']));
                        }
                    } catch (\Exception $e) {
                        $this->warn("Could not read tables from {$file}: " . $e->getMessage());
                    }
                }

                // Extract date from filename (backup_2026-02-21_143025.json)
                $createdAt = null;
                if (preg_match('/backup_(\d{4}-\d{2}-\d{2})_(\d{6})/', $file, $matches)) {
                    $createdAt = $matches[1] . ' ' . substr($matches[2], 0, 2) . ':' . substr($matches[2], 2, 2) . ':' . substr($matches[2], 4, 2);
                } elseif (preg_match('/full_backup_(\d{4}-\d{2}-\d{2})_(\d{6})/', $file, $matches)) {
                    $createdAt = $matches[1] . ' ' . substr($matches[2], 0, 2) . ':' . substr($matches[2], 2, 2) . ':' . substr($matches[2], 4, 2);
                }

                // Create backup record - ONLY using columns that exist in your table
                $backup = new Backup();
                $backup->filename = $file;
                $backup->path = $file;
                $backup->size = $size;
                $backup->type = $type;
                $backup->tables_backed_up = $tablesBackedUp;
                $backup->created_at = $createdAt ?: now();

                // Save without updated_at (your table doesn't have it)
                $backup->timestamps = false; // Disable automatic timestamps
                $backup->save();

                $this->info("✅ Synced {$file} to database (ID: {$backup->id})");
                $synced++;

            } catch (\Exception $e) {
                $this->error("❌ Failed to sync {$file}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info('Sync completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced', $synced],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total Files', count($files)],
            ]
        );

        return 0;
    }
}
