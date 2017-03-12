<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideCanceled;
use Caronae\Models\Ride;

class RideCanceledTest extends TestCase
{
	protected $ride;

	public function setUp() {
        $this->ride = Mockery::mock(Ride::class);
    	$this->ride->shouldReceive('getAttribute')->with('id')->andReturn(1);
	}

	public function testPushNotificationArrayShouldContainAllFields()
    {
    	$notification = new RideCanceled($this->ride);

        $this->assertEquals([
            'message' => 'Um motorista cancelou uma carona ativa sua',
            'msgType' => 'cancelled',
            'rideId'   => 1
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
