protected function schedule(Schedule $schedule)
{
    // Add this to your schedule method:
    $schedule->command('backup:create --type=database')
        ->dailyAt('02:00')
        ->runInBackground()
        ->onSuccess(function () {
            \Log::info('Scheduled database backup completed successfully.');
        })
        ->onFailure(function () {
            \Log::error('Scheduled database backup failed.');
        });
}
protected $middlewareGroups = [
    'web' => [
        // ... other middleware (keep existing ones)
        \App\Http\Middleware\ShareNotifications::class,
    ],
];
