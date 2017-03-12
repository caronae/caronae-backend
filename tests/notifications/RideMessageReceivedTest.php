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
	protected $message;

	public function setUp() {
        $ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('title')->andReturn('ride title');

    	$user = Mockery::mock(User::class);
    	$user->shouldReceive('getAttribute')->with('id')->andReturn(2);
    	$user->shouldReceive('getAttribute')->with('name')->andReturn('Foo');

    	$this->message = Mockery::mock(Message::class);
    	$this->message->shouldReceive('getAttribute')->with('user')->andReturn($user);
    	$this->message->shouldReceive('getAttribute')->with('ride')->andReturn($ride);
    	$this->message->shouldReceive('getAttribute')->with('ride_id')->andReturn(1);
    	$this->message->shouldReceive('getAttribute')->with('body')->andReturn('bar');
	}

	public function testPushNotificationArrayShouldContainAllFields()
    {
    	$notification = new RideMessageReceived($this->message);

        $this->assertEquals([
            'title' => 'ride title',
            'message' => 'Foo: bar',
            'rideId' => 1,
            'senderId' => 2,
            'msgType' => 'chat'
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
