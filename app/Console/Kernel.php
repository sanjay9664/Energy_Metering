<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Existing schedules
        $schedule->command('sync:frontend-data')->everyFiveMinutes();
        $schedule->command('sync:mongodb')->everyFiveMinutes();
        $schedule->command('charge:deduct-daily')->everyTenMinutes();
        
        // New schedule for daily fixed charge deduction at midnight
        $schedule->command('fixedcharge:deduct-daily')
            ->dailyAt('00:00')
            ->timezone('Asia/Kolkata');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}