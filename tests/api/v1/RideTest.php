<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Ride;
use App\RideUser;
use App\Services\PushNotificationService;

class RideTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $headers;

    /**
    * @before
    */
    public function cleanDatabase()
    {
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

    public function testGetAll()
    {
        $user = $this->user;
        $rideIds = [];
        $rides = factory(Ride::class, 'next', 2)->create(['done' => false])->each(function($ride) use ($user, &$rideIds) {
            $ride->users()->attach($user, ['status' => 'driver']);
            $rideIds[] = $ride->id;
        });

        $rides = Ride::findMany($rideIds);
        foreach ($rides as $ride) {
            $ride->driver = $user->toArray();
            unset($ride->done);
        }

        $response = $this->json('GET', 'ride/all', [], $this->headers);
        $response->assertResponseOk();

        $driverArray = [
            'id' => $user->id,
            'name' => $user->name,
            'profile' => $user->profile,
            'course' => $user->course,
            'phone_number' => $user->phone_number,
            'email' => $user->email,
            'car_owner' => $user->car_owner,
            'car_model' => $user->car_model,
            'car_color' => $user->car_color,
            'car_plate' => $user->car_plate,
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'location' => $user->location,
            'face_id' => $user->face_id,
            'profile_pic_url' => $user->profile_pic_url
        ];

        $response->seeJsonEquals([
            [
                'id' => $rides[0]->id,
                'myzone' => $rides[0]->myzone,
                'neighborhood' => $rides[0]->neighborhood,
                'going' => $rides[0]->going,
                'place' => $rides[0]->place,
                'route' => $rides[0]->route,
                'routine_id' => $rides[0]->routine_id,
                'hub' => $rides[0]->hub,
                'slots' => $rides[0]->slots,
                'mytime' => $rides[0]->mytime,
                'mydate' => $rides[0]->mydate,
                'description' => $rides[0]->description,
                'week_days' => $rides[0]->week_days,
                'repeats_until' => $rides[0]->repeats_until,
                'driver' => $driverArray
            ],
            [
                'id' => $rides[1]->id,
                'myzone' => $rides[1]->myzone,
                'neighborhood' => $rides[1]->neighborhood,
                'going' => $rides[1]->going,
                'place' => $rides[1]->place,
                'route' => $rides[1]->route,
                'routine_id' => $rides[1]->routine_id,
                'hub' => $rides[1]->hub,
                'slots' => $rides[1]->slots,
                'mytime' => $rides[1]->mytime,
                'mydate' => $rides[1]->mydate,
                'description' => $rides[1]->description,
                'week_days' => $rides[1]->week_days,
                'repeats_until' => $rides[1]->repeats_until,
                'driver' => $driverArray
            ]
        ]);
    }

    public function testSearch()
    {

    }

    public function testCreateWithoutRoutine()
    {
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '20/10/2016',
            'mytime' => '10:00',
            'week_days' => NULL,
            'repeats_until' => NULL,
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertResponseOk();

        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-20',
            'mytime' => '10:00',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ]);

        $response->seeJsonStructure([
            '*' => ['id']
        ]);
    }

    public function testCreateWithRoutine()
    {
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '24/10/2016',
            'mytime' => '16:40',
            'week_days' => '2,4', // tuesday, thursday
            'repeats_until' => '01/11/2016',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertResponseOk();

        $jsonContent = json_decode($this->response->getContent());
        $this->assertEquals(4, count($jsonContent), "Should create exactly 4 rides.");

        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-24',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);
        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-25',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);
        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-27',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);
        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-11-01',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);

        $response->seeJsonStructure([
            '*' => ['id', 'routine_id', 'week_days']
        ]);
    }

    public function testDelete()
    {
        $ride = factory(Ride::class, 'next')->create(['done' => false]);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $response = $this->json('DELETE', 'ride/' . $ride->id, [], $this->headers);
        $response->assertResponseOk();
    }

    public function testDeleteAllFromRoutine()
    {

    }

    public function testJoin()
    {
        $ride = factory(Ride::class, 'next')->create(['done' => false]);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/requestJoin', $request, $this->headers);
        $response->assertResponseOk();
    }

    public function testGetRequesters()
    {

    }

    public function testAnswerJoinRequest()
    {

    }

    public function testLeave()
    {
        $ride = factory(Ride::class, 'next')->create(['done' => false]);
        $ride->users()->attach($this->user, ['status' => 'accepted']);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/leaveRide', $request, $this->headers);
        $response->assertResponseOk();
    }

    public function testFinish()
    {
        $ride = factory(Ride::class, 'next')->create(['done' => false]);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/finishRide', $request, $this->headers);
        $response->assertResponseOk();
    }

    public function testSaveFeedback()
    {

    }

    public function testGetActiveRides()
    {

    }

    public function testGetHistory()
    {

    }

    public function testGetHistoryCount()
    {

    }

    public function testSendChatMessage()
    {   
        // Mock PushNotification interface
        App::singleton(PushNotificationService::class, function($app) {
            $pushMock = Mockery::mock(PushNotificationService::class);
            $pushMock->shouldReceive('sendDataToRideMembers')->andReturn(array('ok'));
            return $pushMock;
        });

        // Create fake ride with the user as driver
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $request = [
            'message' => str_random(255)
        ];

        $response = $this->json('POST', 'ride/' . $ride->id . '/chat', $request, $this->headers);
        $response->assertResponseOk();
    }
}
