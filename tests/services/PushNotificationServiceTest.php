<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;

use Caronae\Repositories\PushNotificationInterface;
use Caronae\Services\PushNotificationService;
use Caronae\Models\Ride;
use Caronae\Models\User;

class PushNotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testSendNotificationToUser()
    {
        $mockResult = array('notification');
        $user = factory(User::class)->create(['gcm_token' => NULL]);
        $topicId = 'user-' . $user->id;
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendNotificationToTopicId')->with($topicId, $data)->once()->andReturn($mockResult);
        $mock->shouldNotReceive('sendNotificationToDevices');

        $push = new PushNotificationService($mock);
        $result = $push->sendNotificationToUser($user, $data);
        $this->assertEquals($mockResult, $result);
    }

    public function testSendNotificationToUserWithTokenNotifications()
    {
        $mockResult = array('notification');
        $user = factory(User::class)->create(['gcm_token' => 'token']);
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendNotificationToDevices')->with('token', $data)->once()->andReturn($mockResult);
        $mock->shouldNotReceive('sendNotificationToTopicId');

        $push = new PushNotificationService($mock);
        $result = $push->sendNotificationToUser($user, $data);
        $this->assertEquals($mockResult, $result);
    }
}
