<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Repositories\PushNotificationInterface;
use App\Services\PushNotificationService;
use App\Ride;
use App\User;

class PushNotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
    * @before
    */
    public function cleanDatabase()
    {
        $this->beginDatabaseTransaction();

        DB::table('ride_user')->delete();
        DB::table('users')->delete();
        DB::table('rides')->delete();
    }

    public function testSendNotificationToDevices()
    {
        $mockResult = array('notification');
        $devices = [str_random(50), str_random(50)];
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendNotificationToDevices')->with($devices, $data)->once()->andReturn($mockResult);

        $push = new PushNotificationService($mock);
        $result = $push->sendNotificationToDevices($devices, $data);
        $this->assertEquals($mockResult, $result);
    }

    public function testSendDataToRideMembers()
    {
        $mockResult = array('notification');
        $ride = factory(Ride::class)->create();
        $topicId = $ride->id;
        $data = array('data');

        $mock = Mockery::mock(PushNotificationInterface::class);
        $mock->shouldReceive('sendDataToTopicId')->with($topicId, $data)->once()->andReturn($mockResult);

        $push = new PushNotificationService($mock);
        $result = $push->sendDataToRideMembers($ride, $data);
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
