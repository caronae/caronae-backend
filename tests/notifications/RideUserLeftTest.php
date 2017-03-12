<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideUserLeft;
use Caronae\Models\Ride;
use Caronae\Models\User;

class RideUserLeftTest extends TestCase
{
	protected $notification;

	public function setUp()
    {
        $ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('id')->andReturn(1);

    	$user = Mockery::mock(User::class);
    	$user->shouldReceive('getAttribute')->with('id')->andReturn(2);

        $this->notification = new RideUserLeft($ride, $user);
        $this->notification->id = uniqid();
	}

	public function testPushNotificationArrayShouldContainAllFields()
    {
        $this->assertSame([
            'id'       => $this->notification->id,
            'message'  => 'Um caronista desistiu de sua carona',
            'msgType'  => 'quitter',
            'rideId'   => 1,
            'senderId' => 2
        ], $this->notification->toPush(Mockery::mock(User::class)));
    }
}
