<?php

namespace App\Console;

use DateTime;
use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $date = (new DateTime('now', new DateTimeZone("Europe/Paris")))->format("H:i:s d-m-Y");

        if (config('app.env') == 'production') {
            $schedule->call(function () use ($date) {
                // Log::channel('cron')->info('CRON queue:work execute a ' . $date);
                Artisan::call('queue:work --queue=high,default --max-time=55');
            })
                ->everyMinute()
                ->name('worker:1');
            // ->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}