<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
use Mockery;
use Tests\TestCase;

class RideCanceledTest extends TestCase
{
	protected $notification;

    public function setUp()
    {
        $ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $driver = Mockery::mock(User::class);
        $driver->shouldReceive('getAttribute')->with('id')->andReturn(2);
    	$this->notification = new RideCanceled($ride, $driver);
        $this->notification->id = uniqid();
    }

    /** @test */
    public function should_contain_all_fields_in_push()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('status')->andReturn('accepted');

        $payload = $this->notification->toPush($user);

        $this->assertSame([
            'id'       => $this->notification->id,
            'message'  => 'Um motorista cancelou uma carona ativa sua',
            'msgType'  => 'cancelled',
            'rideId'   => 1,
            'senderId' => 2,
        ], $payload);
    }

    /** @test */
    public function should_send_proper_message_for_pending_user()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('status')->andReturn('pending');

        $payload = $this->notification->toPush($user);

        $this->assertEquals('Um motorista cancelou uma carona que você havia solicitado', $payload['message']);
    }
}
