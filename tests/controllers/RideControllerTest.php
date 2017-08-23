<?php

namespace Tests;

use Carbon\Carbon;
use Caronae\Models\Hub;
use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
use Caronae\Notifications\RideFinished;
use Caronae\Notifications\RideJoinRequestAnswered;
use Caronae\Notifications\RideJoinRequested;
use Caronae\Notifications\RideMessageReceived;
use Caronae\Notifications\RideUserLeft;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RideControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $headers;
    protected $push;

    /**
    * @before
    */
    public function createFakeUserHeaders()
    {
        $this->user = factory(User::class)->create()->fresh();
        $this->headers = ['token' => $this->user->token];
    }

    public function testIndexShouldReturnNextRides()
    {
        $user = $this->user;
        $rides = factory(Ride::class, 'next', 2)->create()->each(function($ride) use ($user) {
            $ride->users()->attach($user, ['status' => 'driver']);
            $ride->fresh();
        });

        $rideOld = factory(Ride::class)->create(['date' => '1990-01-01 00:00:00']);
        $rideOld->users()->attach($user, ['status' => 'driver']);

        $response = $this->json('GET', 'rides', [], $this->headers);
        $response->assertStatus(200);

        $response->assertJsonStructure(['data']);
        $response->assertJsonFragment($rides[0]->toArray());
        $response->assertJsonFragment($rides[1]->toArray());
    }

    public function testIndexShouldAllowFiltering()
    {
        $ride1 = factory(Ride::class, 'next')->create(['neighborhood' => 'Ipanema', 'going' => true])->fresh();
        $ride1->users()->attach($this->user, ['status' => 'driver']);

        $ride2 = factory(Ride::class, 'next')->create(['neighborhood' => 'Niterói', 'going' => false])->fresh();
        $ride2->users()->attach($this->user, ['status' => 'driver']);

        $response = $this->json('GET', 'rides', ['neighborhoods' => 'Ipanema'], $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['data' => [ $ride1->toArray() ]]);

        $response = $this->json('GET', 'rides', ['going' => false], $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['data' => [ $ride2->toArray() ]]);
    }

    public function testIndexShouldFilterByCampus()
    {
        $hub = Hub::create(['name' => 'CT1', 'center' => 'CT', 'campus' => 'Cidade Universitária']);
        $hub2 = Hub::create(['name' => 'PV', 'center' => 'PV', 'campus' => 'Praia Vermelha']);

        $ride1 = factory(Ride::class, 'next')->create(['hub' => $hub->name])->fresh();
        $ride1->users()->attach($this->user, ['status' => 'driver']);

        $ride2 = factory(Ride::class, 'next')->create(['hub' => $hub->center])->fresh();
        $ride2->users()->attach($this->user, ['status' => 'driver']);

        $ride3 = factory(Ride::class, 'next')->create(['hub' => $hub2->name])->fresh();
        $ride3->users()->attach($this->user, ['status' => 'driver']);

        $response = $this->json('GET', 'rides', ['campus' => 'Cidade Universitária'], $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['data' => [ $ride1->toArray(), $ride2->toArray() ]]);
    }

    public function testIndexShouldAllowSearchByDateAndTime()
    {
        $futureDate = Carbon::now()->addDays(5)->setTime(12,0,0);
        $ride1 = factory(Ride::class, 'next')->create(['date' => $futureDate])->fresh();
        $ride1->users()->attach($this->user, ['status' => 'driver']);

        $futureDate2 = $futureDate->copy()->setTime(8,0,0);
        $ride2 = factory(Ride::class, 'next')->create(['date' => $futureDate2])->fresh();
        $ride2->users()->attach($this->user, ['status' => 'driver']);

        $pastDate = Carbon::now()->addDays(-5);
        $ride3 = factory(Ride::class)->create(['date' => $pastDate])->fresh();
        $ride3->users()->attach($this->user, ['status' => 'driver']);

        $filterParams = ['date' => $futureDate->format('Y-m-d'), 'time' => '12:00'];
        $response = $this->json('GET', 'rides', $filterParams, $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['data' => [ $ride1->toArray() ]]);

        $filterParams = ['date' => $futureDate->format('Y-m-d')];
        $response = $this->json('GET', 'rides', $filterParams, $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['data' => [ $ride2->toArray(), $ride1->toArray() ]]);
    }

    public function testFindsRide()
    {
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $response = $this->json('GET', 'ride/' . $ride->id, [], $this->headers);

        $response->assertStatus(200);
        $response->assertJson($ride->toArray());
        $response->assertJson(['driver' => $this->user->toArray()]);
        $response->assertJson(['availableSlots' => $ride->availableSlots]);
    }

    public function testShowsRideWebView()
    {
        $ride = factory(Ride::class, 'next')->create([
            'date' => Carbon::parse('2017-07-15 12:00:00'),
            'neighborhood' => 'Ipanema',
            'hub' => 'CT',
            'going' => 'true'
        ]);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $response = $this->json('GET', 'carona/' . $ride->id, [], $this->headers);

        $response->assertStatus(200);
        $response->assertViewIs('rides.showWeb');
        $response->assertViewHas('title', 'Ipanema → CT | 15/07 | 12:00');
        $response->assertViewHas('driver', $this->user->name);
        $response->assertViewHas('deepLinkUrl', 'caronae://carona/' . $ride->id);
    }

    public function testShowsRideNotFoundWebView()
    {
        $response = $this->json('GET', 'carona/666', [], $this->headers);

        $response->assertStatus(404);
        $response->assertViewIs('rides.notFound');
    }

    public function testValidateValidRide()
    {
        $ride = factory(Ride::class)->create(['date' => '2016-12-18 16:00:00', 'going' => false]);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $parameters = [
            'date' => '18/12/2016',
            'time' => '16:00:00',
            'going' => 1
        ];
        $response = $this->json('GET', 'ride/validateDuplicate', $parameters, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'valid' => true,
            'status' => 'valid',
            'message' => 'No conflicting rides were found close to the specified date.'
        ]);
    }

    public function testValidatePossibleDuplicate()
    {
        $ride = factory(Ride::class)->create(['date' => '2016-12-18 10:00:00', 'going' => true]);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $parameters = [
            'date' => '18/12/2016',
            'time' => '16:00:00',
            'going' => 1
        ];
        $response = $this->json('GET', 'ride/validateDuplicate', $parameters, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'valid' => false,
            'status' => 'possible_duplicate',
            'message' => 'The user has already offered a ride too close to the specified date.'
        ]);
    }

    public function testValidateDuplicate()
    {
        $ride = factory(Ride::class)->create(['date' => '2016-12-18 16:20:00', 'going' => true]);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $parameters = [
            'date' => '18/12/2016',
            'time' => '16:00:00',
            'going' => 1
        ];
        $response = $this->json('GET', 'ride/validateDuplicate', $parameters, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'valid' => false,
            'status' => 'duplicate',
            'message' => 'The user has already offered a ride on the specified date.'
        ]);
    }

    public function testCreateWithoutRoutine()
    {
        $date = Carbon::now()->addDays(5);
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => $date->format('d/m/Y'),
            'mytime' => $date->format('H:i:s'),
            'week_days' => NULL,
            'repeats_until' => NULL,
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertStatus(201);

        $response->assertJsonFragment([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => $date->format('Y-m-d'),
            'mytime' => $date->format('H:i:00'),
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ]);

        $response->assertJsonStructure([
            '*' => ['id']
        ]);
    }

    public function testCreateWithRoutine()
    {
        $date = Carbon::now()->addDays(5);
        $repeatsUntil = $date->copy()->addWeek();
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => $date->format('d/m/Y'),
            'mytime' => $date->format('H:i:s'),
            'week_days' => $date->dayOfWeek,
            'repeats_until' => $repeatsUntil->format('d/m/Y'),
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertStatus(201);

        $jsonContent = json_decode($response->getContent());
        $this->assertEquals(2, count($jsonContent), "Should create exactly 2 rides.");

        $response->assertJsonFragment([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => $date->format('Y-m-d'),
            'mytime' => $date->format('H:i:00'),
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);
        $response->assertJsonFragment([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => $repeatsUntil->format('Y-m-d'),
            'mytime' => $date->format('H:i:00'),
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);

        $response->assertJsonStructure([
            '*' => ['id', 'routine_id', 'week_days']
        ]);
    }

    public function testCreateRideInThePastShouldFail()
    {
        $date = Carbon::yesterday();
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => $date->format('d/m/Y'),
            'mytime' => $date->format('H:i:s'),
            'week_days' => NULL,
            'repeats_until' => NULL,
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertStatus(422);
        $response->assertExactJson([
            'mydate' => ['You cannot create a ride in the past.']
        ]);
    }

    public function testDelete()
    {
        $ride = factory(Ride::class, 'next')->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $response = $this->json('DELETE', 'ride/' . $ride->id, [], $this->headers);
        $response->assertStatus(200);
    }

    public function testDeleteAllFromRoutine()
    {
        $ride = factory(Ride::class, 'next')->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);
        $ride->routine_id = $ride->id;
        $ride->save();

        $response = $this->json('DELETE', 'ride/allFromRoutine/' . $ride->id, [], $this->headers);
        $response->assertStatus(200);
    }

    public function testJoin()
    {
        $ride = factory(Ride::class, 'next')->create();
        $user = factory(User::class)->create();
        $ride->users()->attach($user, ['status' => 'driver']);

        $this->expectsNotification($user, RideJoinRequested::class);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/requestJoin', $request, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Request sent.'
        ]);
    }

    public function testGetRequesters()
    {
        $ride = factory(Ride::class, 'next')->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $user = factory(User::class)->create();
        $ride->users()->attach($user, ['status' => 'pending']);

        $ride->users()->attach(factory(User::class)->create(), ['status' => 'accepted']);
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'rejected']);

        $response = $this->json('GET', 'ride/getRequesters/' . $ride->id, [], $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            [
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
            ]
        ]);
    }

    public function testAnswerJoinRequest()
    {
        $ride = factory(Ride::class, 'next')->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $rider = factory(User::class)->create();
        $ride->users()->attach($rider, ['status' => 'pending']);

        $this->expectsNotification($rider, RideJoinRequestAnswered::class);

        $request = [
            'rideId' => $ride->id,
            'userId' => $rider->id,
            'accepted' => true
        ];

        $response = $this->json('POST', 'ride/answerJoinRequest', $request, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Request answered.'
        ]);
    }

    public function testLeaveAsUser()
    {
        $ride = factory(Ride::class, 'next')->create();
        $driver = factory(User::class)->create();
        $ride->users()->attach($driver, ['status' => 'driver']);

        $ride->users()->attach($this->user, ['status' => 'accepted']);

        $this->expectsNotification($driver, RideUserLeft::class);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/leaveRide', $request, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Left ride.'
        ]);
    }

    public function testLeaveAsDriver()
    {
        $ride = factory(Ride::class, 'next')->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $rider = factory(User::class)->create();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        $this->expectsNotification($rider, RideCanceled::class);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/leaveRide', $request, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Left ride.'
        ]);
    }

    public function testFinishOldRideSucceeds()
    {
        $ride = factory(Ride::class)->create(['date' => '1990-01-01 00:00:00']);
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $rider = factory(User::class)->create();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        $this->expectsNotification($rider, RideFinished::class);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/finishRide', $request, $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Ride finished.'
        ]);
    }

    public function testFinishFutureRideFails()
    {
        $ride = factory(Ride::class, 'next')->create();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $request = [
            'rideId' => $ride->id
        ];

        $response = $this->json('POST', 'ride/finishRide', $request, $this->headers);
        $response->assertStatus(403);
        $response->assertExactJson([
            'error' => 'A ride in the future cannot be marked as finished'
        ]);
    }

    public function testGetHistory()
    {
        $user2 = factory(User::class)->create()->fresh();

        $ride1 = factory(Ride::class)->create(['done' => true]);
        $ride2 = factory(Ride::class)->create(['done' => true]);
        $ride3 = factory(Ride::class)->create(['done' => false]);
        $ride4 = factory(Ride::class, 'next')->create();
        $ride5 = factory(Ride::class)->create(['done' => true]);
        $ride6 = factory(Ride::class)->create(['done' => true]);

        $ride1->users()->attach($this->user, ['status' => 'driver']);
        $ride2->users()->attach($this->user, ['status' => 'accepted']);
        $ride2->users()->attach($user2, ['status' => 'driver']);
        $ride3->users()->attach($this->user, ['status' => 'driver']);
        $ride4->users()->attach($this->user, ['status' => 'accepted']);
        $ride5->users()->attach($user2, ['status' => 'driver']);
        $ride6->users()->attach($this->user, ['status' => 'rejected']);

        $response = $this->json('GET', 'ride/getRidesHistory', [], $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'id' => $ride1->id,
                'myzone' => $ride1->myzone,
                'neighborhood' => $ride1->neighborhood,
                'going' => $ride1->going,
                'place' => $ride1->place,
                'route' => $ride1->route,
                'routine_id' => $ride1->routine_id,
                'hub' => $ride1->hub,
                'slots' => $ride1->slots,
                'mytime' => $ride1->date->format('H:i:s'),
                'mydate' => $ride1->date->format('Y-m-d'),
                'description' => $ride1->description,
                'week_days' => $ride1->week_days,
                'repeats_until' => $ride1->repeats_until,
                'driver' => $this->user->toArray(),
                'riders' => [],
                'feedback' => null
            ],
            [
                'id' => $ride2->id,
                'myzone' => $ride2->myzone,
                'neighborhood' => $ride2->neighborhood,
                'going' => $ride2->going,
                'place' => $ride2->place,
                'route' => $ride2->route,
                'routine_id' => $ride2->routine_id,
                'hub' => $ride2->hub,
                'slots' => $ride2->slots,
                'mytime' => $ride2->date->format('H:i:s'),
                'mydate' => $ride2->date->format('Y-m-d'),
                'description' => $ride2->description,
                'week_days' => $ride2->week_days,
                'repeats_until' => $ride2->repeats_until,
                'driver' => $user2->toArray(),
                'riders' => [$this->user->toArray()],
                'feedback' => null
            ]
        ]);
    }

    public function testGetHistoryCount()
    {
        $user2 = factory(User::class)->create();

        $ride1 = factory(Ride::class)->create(['done' => true]); // offered
        $ride1->users()->attach($this->user, ['status' => 'driver']);

        $ride1 = factory(Ride::class)->create(['done' => true]); // offered
        $ride1->users()->attach($this->user, ['status' => 'driver']);

        $ride2 = factory(Ride::class)->create(['done' => true]); // taken
        $ride2->users()->attach($this->user, ['status' => 'accepted']);
        $ride2->users()->attach($user2, ['status' => 'driver']);

        $ride3 = factory(Ride::class)->create(['done' => false]); // incomplete
        $ride3->users()->attach($this->user, ['status' => 'driver']);

        $ride4 = factory(Ride::class, 'next')->create(); // incomplete
        $ride4->users()->attach($this->user, ['status' => 'accepted']);

        $ride5 = factory(Ride::class)->create(['done' => true]); // from other user
        $ride5->users()->attach($user2, ['status' => 'driver']);

        $ride6 = factory(Ride::class)->create(['done' => true]); // rejected
        $ride6->users()->attach($this->user, ['status' => 'rejected']);

        $response = $this->json('GET', 'ride/getRidesHistoryCount/' . $this->user->id, [], $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'offeredCount' => 2,
            'takenCount' => 1
        ]);
    }

    public function testGetChatMessages()
    {
        $ride = factory(Ride::class)->create()->fresh();
        $ride->users()->attach($this->user, ['status' => 'driver']);

        $messages = factory(Message::class, 3)->create([
            'ride_id' => $ride->id,
            'user_id' => $this->user->id
        ])->sortBy('created_at')->values()->map(function ($message) {
            return [
                'id' => $message->id,
                'body' => $message->body,
                'user' => $message->user->toArray(),
                'date' => $message->date->toDateTimeString(),
            ];
        })->all();

        $response = $this->json('GET', 'ride/' . $ride->id . '/chat', [], $this->headers);
        $response->assertStatus(200);
        $response->assertExactJson([
            'messages' => $messages
        ]);
    }

    public function testSendChatMessage()
    {
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($this->user, ['status' => 'accepted']);

        $user2 = factory(User::class)->create();
        $ride->users()->attach($user2, ['status' => 'accepted']);
        $user3 = factory(User::class)->create();
        $ride->users()->attach($user3, ['status' => 'driver']);

        $request = [
            'message' => str_random(255)
        ];

        // all users should be notified except the sender
        $this->expectsNotification($user2, RideMessageReceived::class);
        $this->expectsNotification($user3, RideMessageReceived::class);

        $response = $this->json('POST', 'ride/' . $ride->id . '/chat', $request, $this->headers);
        $response->assertStatus(201);
    }
}
