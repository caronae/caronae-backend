<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Notifications\RideJoinRequested;
use Caronae\Models\Ride;
use Caronae\Models\User;

class RideJoinRequestedTest extends TestCase
{
    public function testPushNotificationArrayShouldContainAllFields()
    {
    	$ride = Mockery::mock(Ride::class);
    	$ride->shouldReceive('getAttribute')->with('id')->andReturn(1);
    	$user = Mockery::mock(User::class);
    	$user->shouldReceive('getAttribute')->with('id')->andReturn(2);
    	$notification = new RideJoinRequested($ride, $user);

        $this->assertEquals([
    		'message'  => 'Sua carona recebeu uma solicitação',
            'msgType'  => 'joinRequest',
            'rideId'   => 1,
            'senderId' => 2
        ], $notification->toPush(Mockery::mock(User::class)));
    }
}
