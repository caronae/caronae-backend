<?php

namespace Caronae\Console;

use Caronae\Console\Commands\RemoveBrokenProfilePictureURLs;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        RemoveBrokenProfilePictureURLs::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:clean')->daily();
        $schedule->command('backup:run')->daily();
    }
}
