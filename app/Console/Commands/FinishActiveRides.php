<?php

namespace Caronae\Console\Commands;

use Carbon\Carbon;
use Caronae\Models\Ride;
use Caronae\Notifications\RideFinished;
use Illuminate\Console\Command;

class FinishActiveRides extends Command
{
    protected $signature = 'ride:finish-active';
    protected $description = 'Mark past active rides as finished';

    public function handle()
    {
        $untilDate = new Carbon('2 hours ago');
        $rides = Ride::where('date', '<', $untilDate)
            ->notFinished()
            ->has('riders');

        $rides->get()->each(function (Ride $ride) {
            $notification = new RideFinished($ride, true);
            $ride->driver()->notify($notification);
            $ride->riders->each->notify($notification);
        });

        $rides->update(['done' => true]);
    }
}
