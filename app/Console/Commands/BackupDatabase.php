<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupDatabase extends Command
{
    protected $signature = 'backup:create {--type=database : Backup type (database or full)} {--scheduled : Run as scheduled backup}';
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
        $isScheduled = $this->option('scheduled');

        try {
            if ($isScheduled) {
                $this->info('Running scheduled backup check...');
                if ($this->backupService->runScheduledBackupIfDue()) {
                    $this->info('Scheduled backup completed successfully.');
                } else {
                    $this->info('No backup was due at this time.');
                }
                return Command::SUCCESS;
            }

            // Manual backup
            if ($type === 'full') {
                $backup = $this->backupService->createFullBackup();
                $this->info('Full backup created: ' . $backup->filename);
            } else {
                $backup = $this->backupService->createDatabaseBackup();
                $this->info('Database backup created: ' . $backup->filename);
            }

            // Clean up old backups
            $settings = $this->backupService->getScheduleSettings();
            $cleaned = $this->backupService->cleanupOldBackups($settings['retention_days']);
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
