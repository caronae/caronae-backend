<?php
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Repositories\PushNotificationInterface;
use Caronae\Services\PushNotificationService;
use Caronae\Models\Ride;
use Caronae\Models\User;

class PushNotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testSendNotificationToRideMembers()
    {
        $mockResult = array('notification');
        $ride = factory(Ride::class)->create();
        $topicId = $ride->id;
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendNotificationToTopicId')->with($topicId, $data, true)->once()->andReturn($mockResult);

        $push = new PushNotificationService($mock);
        $result = $push->sendNotificationToRideMembers($ride, $data);
        $this->assertEquals($mockResult, $result);
    }

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

    public function testSendDataToUser()
    {
        $mockResult = array('notification');
        $user = factory(User::class)->create();
        $topicId = 'user-' . $user->id;
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendDataToTopicId')->with($topicId, $data)->once()->andReturn($mockResult);

        $push = new PushNotificationService($mock);
        $result = $push->sendDataToUser($user, $data);
        $this->assertEquals($mockResult, $result);
    }
}
