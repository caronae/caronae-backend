<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideFinished;
use Caronae\Models\Ride;

class RideFinishedTest extends TestCase
{
	protected $notification;

	public function setUp()
    {
        $ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('id')->andReturn(1);
    	$this->notification = new RideFinished($ride);
    }

    public function testPushNotificationArrayShouldContainAllFields()
    {

        $this->assertEquals([
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
            'rideId'   => 1
        ], $this->notification->toPush(Mockery::mock(User::class)));
    }
}
