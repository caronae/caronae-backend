<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideFinished;
use Caronae\Models\Ride;

class RideFinishedTest extends TestCase
{
	protected $ride;

	public function setUp() {
        $this->ride = Mockery::mock(Ride::class);
    	$this->ride->shouldReceive('getAttribute')->with('id')->andReturn(1);
	}

	public function testPushNotificationArrayShouldContainAllFields()
    {
    	$notification = new RideFinished($this->ride);

        $this->assertEquals([
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
            'rideId'   => 1
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
