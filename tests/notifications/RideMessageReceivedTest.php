<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideMessageReceived;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Models\Message;

class RideMessageReceivedTest extends TestCase
{
	protected $notification;

	public function setUp()
    {
        $ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('title')->andReturn('ride title');

    	$user = Mockery::mock(User::class);
    	$user->shouldReceive('getAttribute')->with('id')->andReturn(2);
    	$user->shouldReceive('getAttribute')->with('name')->andReturn('Foo');

    	$message = Mockery::mock(Message::class);
        $message->shouldReceive('getAttribute')->with('id')->andReturn(123);
    	$message->shouldReceive('getAttribute')->with('user')->andReturn($user);
    	$message->shouldReceive('getAttribute')->with('ride')->andReturn($ride);
    	$message->shouldReceive('getAttribute')->with('ride_id')->andReturn(1);
    	$message->shouldReceive('getAttribute')->with('body')->andReturn('bar');

        $this->notification = new RideMessageReceived($message);
	}

	public function testPushNotificationArrayShouldContainAllFields()
    {
        $this->assertSame([
            'id' => '123',
            'title' => 'ride title',
            'message' => 'Foo: bar',
            'rideId' => 1,
            'senderId' => 2,
            'msgType' => 'chat'
        ], $this->notification->toPush(Mockery::mock(User::class)));
    }
}
