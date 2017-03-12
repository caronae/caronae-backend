<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideCanceled;
use Caronae\Models\Ride;

class RideCanceledTest extends TestCase
{
	protected $notification;

	public function setUp()
    {
        $ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('id')->andReturn(1);
    	$this->notification = new RideCanceled($ride);
        $this->notification->id = uniqid();
    }

    public function testPushNotificationArrayShouldContainAllFields()
    {
        $this->assertSame([
            'id'      => $this->notification->id,
            'message' => 'Um motorista cancelou uma carona ativa sua',
            'msgType' => 'cancelled',
            'rideId'  => 1
        ], $this->notification->toPush(Mockery::mock(User::class)));
    }
}
