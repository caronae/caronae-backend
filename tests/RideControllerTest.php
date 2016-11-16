<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Ride;
use App\RideUser;
use App\Http\PostGCM;
use App\Http\Controllers\RideController;

class RideControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $headers;

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

    /**
    * @before
    */
    public function createFakeUserHeaders()
    {
        $user = factory(User::class)->create();
        $this->user = User::find($user->id);
        $this->headers = ['token' => $this->user->token];
    }

    public function testSendChatMessage()
    {
        // Create fake ride with the user as driver
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);
        $topic = "/topics/" . $ride->id;
        $message = str_random(255);

        $request = [
            'message' => $message
        ];

        // Mock Siga interface
        App::singleton(PostGCM::class, function($app) use ($topic, $message) {
            $gcmMock = Mockery::mock(PostGCM::class);
            $gcmMock->shouldReceive('sendDataToTopic')->once()->with($topic, $message)->andReturn(array('ok'));
            return $gcmMock;
        });


        // $response = $this->json('POST', 'ride/' . $ride->id . '/chat', $request, $this->headers);
    }

}
