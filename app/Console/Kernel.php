<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\SaveFrameCommand',
        'App\Console\Commands\MakeGifCommand',
        'App\Console\Commands\GetSunDataCommand',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $interval = env('TIME_LAPSE_GIF_CAPTURE_INTERVAL');
        $interval = intval($interval);

        if (!$interval) {
            return false;
        }

        $schedule->command('cam:save')->cron("*/$interval * * * * *");
    }
}
