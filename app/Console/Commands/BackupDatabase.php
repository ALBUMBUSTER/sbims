<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupDatabase extends Command
{
    protected $signature = 'backup:create {--type=database : Backup type (database or full)}';
    protected $description = 'Create a database or full backup';

    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    public function handle()
    {
        $type = $this->option('type');

        try {
            if ($type === 'full') {
                $backup = $this->backupService->createFullBackup();
                $this->info('Full backup created: ' . $backup->filename);
            } else {
                $backup = $this->backupService->createDatabaseBackup();
                $this->info('Database backup created: ' . $backup->filename);
            }

            // Clean up old backups
            $cleaned = $this->backupService->cleanupOldBackups(30);
            if ($cleaned > 0) {
                $this->info("Cleaned up {$cleaned} old backups.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
