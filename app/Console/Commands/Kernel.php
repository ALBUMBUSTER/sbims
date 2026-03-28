<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check for scheduled backups every minute
        $schedule->command('backup:create --scheduled')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/backup-scheduler.log'));
        $schedule->command('hearings:check-overdue')->dailyAt('00:00');
        $schedule->command('blotters:check-deadlines')->dailyAt('00:00');
        // Alternative: Run backup at specific times based on settings
        // This will be handled by the BackupService's runScheduledBackupIfDue method
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
