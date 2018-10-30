<?php

namespace Caronae\Console\Commands;

use Carbon\Carbon;
use Caronae\Models\Ride;
use Illuminate\Console\Command;

class FinishActiveRides extends Command
{
    protected $signature = 'ride:finish-active';
    protected $description = 'Mark past active rides as finished';

    public function handle()
    {
        $untilDate = new Carbon('2 hours ago');
        Ride::where('date', '<', $untilDate)
            ->where('done', false)
            ->has('riders')
            ->update(['done' => true]);
    }
}
