<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideJoinRequestAnswered;
use Mockery;
use Tests\TestCase;

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

    /** @test */
    public function should_contain_all_fields_in_push_when_accepted()
    {
    	$notification = new RideJoinRequestAnswered($this->ride, true);
        $notification->id = uniqid();

        $this->assertSame([
            'id'       => $notification->id,
            'message'  => 'Você foi aceito em uma carona =)',
            'msgType'  => 'accepted',
            'rideId'   => 1,
            'senderId' => 2
        ], $notification->toPush(Mockery::mock(User::class)));
    }

    /** @test */
    public function should_contain_all_fields_in_push_when_rejected()
    {
    	$notification = new RideJoinRequestAnswered($this->ride, false);
        $notification->id = uniqid();

        $this->assertEquals([
            'id'       => $notification->id,
            'message'  => 'Você foi recusado em uma carona =(',
            'msgType'  => 'refused',
            'rideId'   => 1,
            'senderId' => 2,
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
