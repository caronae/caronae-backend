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
        $this->notification->id = uniqid();
    }

    public function testPushNotificationArrayShouldContainAllFields()
    {
        $this->assertSame([
            'id'      => $this->notification->id,
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
            'rideId'  => 1
        ], $this->notification->toPush(Mockery::mock(User::class)));
    }
}
