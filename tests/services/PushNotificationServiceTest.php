<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;

use Caronae\Repositories\PushNotificationInterface;
use Caronae\Services\PushNotificationService;
use Caronae\Models\User;

class PushNotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testNotificationsAreSentToUserTopic()
    {
        $mockResult = array('notification');
        $user = factory(User::class)->create();
        $topicId = 'user-' . $user->id;
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendNotificationToTopicId')->with($topicId, $data)->once()->andReturn($mockResult);

        $push = new PushNotificationService($mock);
        $result = $push->sendNotificationToUser($user, $data);
        $this->assertEquals($mockResult, $result);
    }
}
