<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideJoinRequestAnswered;
use Caronae\Models\Ride;
use Caronae\Models\User;

class RideJoinRequestAnsweredTest extends TestCase
{
	private $ride;

	public function setUp()
    {
        $this->ride = Mockery::mock(Ride::class);
    	$this->ride->shouldReceive('getAttribute')->with('id')->andReturn(1);

    	$driver = Mockery::mock(User::class);
    	$driver->shouldReceive('getAttribute')->with('id')->andReturn(2);
    	$this->ride->shouldReceive('driver')->andReturn($driver);
	}

    public function testPushNotificationArrayShouldContainAllFields_whenAccepted()
    {
    	$notification = new RideJoinRequestAnswered($this->ride, true);

        $this->assertEquals([
            'message'  => 'Você foi aceito em uma carona =)',
            'msgType'  => 'accepted',
            'rideId'   => 1,
            'senderId' => 2
        ], $notification->toPush(Mockery::mock(User::class)));
    }

    public function testPushNotificationArrayShouldContainAllFields_whenRejected()
    {
    	$notification = new RideJoinRequestAnswered($this->ride, false);

        $this->assertEquals([
            'message'  => 'Você foi recusado em uma carona =(',
            'msgType'  => 'refused',
            'rideId'   => 1,
            'senderId' => 2
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
