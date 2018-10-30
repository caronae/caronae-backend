<?php

namespace Caronae\Console\Commands;

use Carbon\Carbon;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideFinished;
use DateTime;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FinishActiveRidesTest extends TestCase
{
    use DatabaseTransactions;

    private $command;

    protected function setUp()
    {
        parent::setUp();
        Notification::fake();
        $this->command = new FinishActiveRides();
    }

    /** @test */
    public function should_finish_active_rides()
    {
        $rideActive = $this->createActiveRide(new Carbon('3 hours 5 minutes ago'));

        $this->command->handle();

        $this->assertDatabaseHas('rides', ['id' => $rideActive->id, 'done' => true]);
    }

    /** @test */
    public function should_not_finish_active_rides_that_might_still_be_in_progress()
    {
        $rideActive = $this->createActiveRide(new Carbon('1 hour 55 minutes ago'));

        $this->command->handle();

        $this->assertDatabaseHas('rides', ['id' => $rideActive->id, 'done' => false]);
    }

    /** @test */
    public function should_not_finish_inactive_rides_as_finished()
    {
        $rideNotActive = $this->createInactiveRide(new Carbon('2 hours 5 minutes ago'));

        $this->command->handle();

        $this->assertDatabaseHas('rides', ['id' => $rideNotActive->id, 'done' => false]);
    }

    /** @test */
    public function should_notify_users_when_ride_is_finished()
    {
        $ride = $this->createActiveRide(new Carbon('2 hours 5 minutes ago'));

        $this->command->handle();

        Notification::assertSentTo($ride->riders, RideFinished::class, function ($notification) {
            return $notification->automated;
        });

        Notification::assertSentTo($ride->driver(), RideFinished::class, function ($notification) {
            return $notification->automated;
        });
    }

    private function createActiveRide(DateTime $date)
    {
        $ride = $this->createInactiveRide($date);

        $rider = factory(User::class)->create();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        return $ride;
    }

    private function createInactiveRide(DateTime $date)
    {
        $driver = factory(User::class)->create();

        $rideActive = factory(Ride::class, 'next')->create(['date' => $date]);
        $rideActive->users()->attach($driver, ['status' => 'driver']);

        return $rideActive;
    }
}
