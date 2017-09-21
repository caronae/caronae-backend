<?php

namespace Tests;

use Caronae\Models\Institution;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testStoreSavesUser()
    {
        $user = $this->newUser();
        $response = $this->json('POST', 'users', $user, $this->institutionAuthorizationHeaders());

        $createdUser = User::where('id_ufrj', $user['id_ufrj'])->first();

        $response->assertStatus(200);
        $response->assertJson(['user' => $createdUser->toArray()]);
        $response->assertJsonStructure(['token']);
    }

    public function testStoreDoesNotAddDuplicatedUser()
    {
        $user = $this->newUser();
        $existingUser = User::create($user);
        $existingUser->generateToken();
        $existingUser->save();

        $response = $this->json('POST', 'users', $user, $this->institutionAuthorizationHeaders());

        $response->assertStatus(200);
        $response->assertJson(['user' => $existingUser->fresh()->toArray()]);
        $response->assertJsonStructure(['token']);
    }

    public function testStoreDoesNotChangeExistingToken()
    {
        $user = $this->newUser();
        $existingUser = User::create($user);
        $existingUser->generateToken();
        $existingUser->save();
        $oldToken = $existingUser->token;

        $data = array_merge($user, [ 'token' => 'new token']);
        $response = $this->json('POST', 'users', $data, $this->institutionAuthorizationHeaders());

        $response->assertStatus(200);
        $this->assertEquals($oldToken, $existingUser->fresh()->token);
    }

    public function testSignInWithValidUserSucceeds()
    {
        // create user with some rides
        $user = factory(User::class)->create();
        $user = $user->fresh();

        // add unfinished rides, which should be returned
        $ride = factory(Ride::class)->create(['done' => false])->fresh();
        $user->rides()->attach($ride, ['status' => 'driver']);

        // add finished ride, which shouldn't be returned
        $rideFinished = factory(Ride::class)->create(['done' => true]);
        $user->rides()->attach($rideFinished, ['status' => 'driver']);

        // add random ride from another user
        factory(Ride::class)->create();

        $response = $this->json('POST', 'user/login', [
            'id_ufrj' => $user->id_ufrj,
            'token' => $user->token
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'user' => [
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
            ],
            'rides' => [
                [
                    'id' => $ride->id,
                    'myzone' => $ride->myzone,
                    'neighborhood' => $ride->neighborhood,
                    'going' => $ride->going,
                    'place' => $ride->place,
                    'route' => $ride->route,
                    'routine_id' => $ride->routine_id,
                    'hub' => $ride->hub,
                    'slots' => $ride->slots,
                    'mytime' => $ride->date->format('H:i:s'),
                    'mydate' => $ride->date->format('Y-m-d'),
                    'description' => $ride->description,
                    'week_days' => $ride->week_days,
                    'repeats_until' => $ride->repeats_until
                ]
            ]
        ]);
    }

    public function testSignInWithInvalidUserFails()
    {
        $response = $this->json('POST', 'user/login', [
            'id_ufrj' => str_random(11),
            'token' => str_random(6)
        ]);
        $response->assertStatus(401);
        $response->assertExactJson([
            'error' => 'User not found with provided credentials.'
        ]);
    }

    public function testUpdateWithValidUserSucceeds()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];
        $body = [
            'phone_number' => '021998781890',
            'email' => 'test@example.com',
            'location' => 'Madureira',
            'car_owner' => true,
            'car_model' => 'Fiat Uno',
            'car_color' => 'azul',
            'car_plate' => 'ABC-1234',
            'profile_pic_url' => 'http://example.com/image.jpg'
        ];

        $response = $this->json('PUT', 'user', $body, $headers);
        $response->assertStatus(200);

        $user = $user->fresh();
        $this->assertEquals($body['phone_number'], $user->phone_number);
        $this->assertEquals($body['email'], $user->email);
        $this->assertEquals($body['location'], $user->location);
        $this->assertEquals($body['car_owner'], $user->car_owner);
        $this->assertEquals($body['car_model'], $user->car_model);
        $this->assertEquals($body['car_plate'], $user->car_plate);
        $this->assertEquals($body['profile_pic_url'], $user->profile_pic_url);
    }

    public function testUpdateWithInvalidUserFails()
    {
        $headers = ['token' => ''];
        $body = [
            'phone_number' => '021999999999',
            'email' => 'test@example.com',
            'location' => 'Somewhere',
            'car_owner' => true,
            'car_model' => 'Car',
            'car_color' => 'color',
            'car_plate' => 'ABC-1234',
            'profile_pic_url' => 'http://example.com/image.jpg'
        ];

        $response = $this->json('PUT', 'user', $body, $headers);
        $response->assertStatus(401);
    }

    public function testGetOfferedRides()
    {
        // create user with some rides
        $user = factory(User::class)->create()->fresh();

        // add unfinished rides, which should be returned
        $ride = factory(Ride::class, 'next')->create(['done' => false])->fresh();
        $ride->users()->attach($user, ['status' => 'driver']);
        $rider = factory(User::class)->create()->fresh();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        // add finished ride, which shouldn't be returned
        $rideFinished = factory(Ride::class)->create(['done' => true]);
        $user->rides()->attach($rideFinished, ['status' => 'driver']);

        // add old ride, which shouldn't be returned
        $rideOld = factory(Ride::class)->create(['date' => '1990-01-01 00:00:00']);
        $user->rides()->attach($rideOld, ['status' => 'driver']);

        // add random ride from another user
        factory(Ride::class)->create();

        $response = $this->json('GET', 'user/' . $user->id . '/offeredRides', [], [
            'token' => $user->token
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'rides' => [
                [
                    'id' => $ride->id,
                    'myzone' => $ride->myzone,
                    'neighborhood' => $ride->neighborhood,
                    'going' => $ride->going,
                    'place' => $ride->place,
                    'route' => $ride->route,
                    'routine_id' => $ride->routine_id,
                    'hub' => $ride->hub,
                    'slots' => $ride->slots,
                    'mytime' => $ride->date->format('H:i:s'),
                    'mydate' => $ride->date->format('Y-m-d'),
                    'description' => $ride->description,
                    'week_days' => $ride->week_days,
                    'repeats_until' => $ride->repeats_until,
                    'riders' => [
                        [
                            'id' => $rider->id,
                            'name' => $rider->name,
                            'profile' => $rider->profile,
                            'course' => $rider->course,
                            'phone_number' => $rider->phone_number,
                            'email' => $rider->email,
                            'car_owner' => $rider->car_owner,
                            'car_model' => $rider->car_model,
                            'car_color' => $rider->car_color,
                            'car_plate' => $rider->car_plate,
                            'created_at' => $rider->created_at->format('Y-m-d H:i:s'),
                            'location' => $rider->location,
                            'face_id' => $rider->face_id,
                            'profile_pic_url' => $rider->profile_pic_url
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testGetOfferedRidesFromAnotherUserShouldError()
    {
        $user = factory(User::class)->create()->fresh();
        $user2 = factory(User::class)->create()->fresh();

        $response = $this->json('GET', 'user/' . $user2->id . '/offeredRides', [], [
            'token' => $user->token
        ]);

        $response->assertStatus(403);
    }

    public function testSaveFacebookID()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];
        $newId = str_random(50);

        $response = $this->json('PUT', 'user/saveFaceId', [
            'id' => $newId
        ], $headers);
        $response->assertStatus(200);

        $savedUser = $user->fresh();
        $this->assertEquals($newId, $savedUser->face_id);
    }

    public function testSaveProfilePictureURL()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];
        $newURL = Factory::create()->url;

        $response = $this->json('PUT', 'user/saveProfilePicUrl', [
            'url' => $newURL
        ], $headers);
        $response->assertStatus(200);

        $savedUser = $user->fresh();
        $this->assertEquals($newURL, $savedUser->profile_pic_url);
    }

    private function institutionAuthorizationHeaders()
    {
        $institution = factory(Institution::class)->create();
        return [
            'Authorization' => 'Basic ' . base64_encode($institution->id . ':' . $institution->password)
        ];
    }

    private function newUser()
    {
        return [
            'name' => 'Foo Bar',
            'profile' => 'Aluno',
            'id_ufrj' => '111',
            'course' => 'Course'
        ];
    }
}
