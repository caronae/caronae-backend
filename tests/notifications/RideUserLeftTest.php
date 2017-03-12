<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideUserLeft;
use Caronae\Models\Ride;
use Caronae\Models\User;

class RideUserLeftTest extends TestCase
{
	protected $ride;
	protected $user;

	public function setUp() {
        $this->ride = Mockery::mock(Ride::class);
    	$this->ride->shouldReceive('getAttribute')->with('id')->andReturn(1);

    	$this->user = Mockery::mock(User::class);
    	$this->user->shouldReceive('getAttribute')->with('id')->andReturn(2);
	}

	public function testPushNotificationArrayShouldContainAllFields()
    {
    	$notification = new RideUserLeft($this->ride, $this->user);

        $this->assertEquals([
            'message'  => 'Um caronista desistiu de sua carona',
            'msgType'  => 'quitter',
            'rideId'   => 1,
            'senderId' => 2
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
