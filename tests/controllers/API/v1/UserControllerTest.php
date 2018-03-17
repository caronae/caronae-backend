<?php

namespace Tests;

use Caronae\Http\Resources\InstitutionResource;
use Caronae\Models\Institution;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $institution;

    public function setUp()
    {
        parent::setUp();
        $this->institution = factory(Institution::class)->create();
    }

    /**
     * @test
     */
    public function shouldCreateUser()
    {
        $user = $this->newUser();
        $response = $this->json('POST', 'api/v1/users', $user, $this->institutionAuthorizationHeaders());

        $createdUser = User::where('id_ufrj', $user['id_ufrj'])->first();

        $response->assertStatus(200);
        $response->assertJson(['user' => $createdUser->toArray()]);
        $response->assertJsonStructure(['token']);
    }

    /**
     * @test
     */
    public function shouldNotCreateUserWithoutAuthorizationHeaders()
    {
        $user = $this->newUser();
        $response = $this->json('POST', 'api/v1/users', $user);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('users', $user);
    }

    /**
     * @test
     */
    public function shouldNotCreateDuplicatedUser()
    {
        $user = $this->newUser();
        $user['institution_id'] = $this->institution->id;
        $existingUser = User::create($user);
        $existingUser->generateToken();
        $existingUser->save();

        $response = $this->json('POST', 'api/v1/users', $user, $this->institutionAuthorizationHeaders());

        $response->assertStatus(200);
        $response->assertJson(['user' => $existingUser->fresh()->toArray()]);
        $response->assertJsonStructure(['token']);
    }

    /**
     * @test
     */
    public function shouldNotChangeTokenWhenUserAlreadyExists()
    {
        $user = $this->newUser();
        $user['institution_id'] = $this->institution->id;
        $existingUser = User::create($user);
        $existingUser->generateToken();
        $existingUser->save();
        $oldToken = $existingUser->token;

        $data = array_merge($user, [ 'token' => 'new token']);
        $response = $this->json('POST', 'api/v1/users', $data, $this->institutionAuthorizationHeaders());

        $response->assertStatus(200);
        $this->assertEquals($oldToken, $existingUser->fresh()->token);
    }

    /**
     * @test
     */
    public function shouldSignIn()
    {
        $user = $this->someUser();

        $response = $this->json('POST', 'api/v1/users/login', [
            'id_ufrj' => $user->id_ufrj,
            'token' => $user->token
        ]);

        $response->assertStatus(200);
        $response->assertJson([
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
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnInstitutionOnSignIn()
    {
        $user = $this->someUser();
        $institution = $user->institution()->first();

        $response = $this->json('POST', 'api/v1/users/login', [
            'id_ufrj' => $user->id_ufrj,
            'token' => $user->token
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'institution' => (new InstitutionResource($institution))->resolve(),
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnUserRidesOnLegacySignIn()
    {
        $user = $this->someUser();

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

    /**
     * @test
     */
    public function shouldNotSignInWithInvalidUser()
    {
        $response = $this->json('POST', 'api/v1/users/login', [
            'id_ufrj' => str_random(11),
            'token' => str_random(6)
        ]);
        $response->assertStatus(401);
        $response->assertExactJson([
            'error' => 'User not found with provided credentials.'
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnUser()
    {
        $user = $this->someUser();
        $response = $this->jsonAs($user, 'GET', 'api/v1/users/' . $user->id);
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
        ]);
    }

    /**
     * @test
     */
    public function shouldNotReturnOtherUser()
    {
        $user = $this->someUser();
        $user2 = $this->someUser();

        $response = $this->jsonAs($user,'GET', 'api/v1/users/' . $user2->id);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function shouldUpdateUserProfileWithLegacyAPI()
    {
        $user = $this->someUser();
        $headers = ['token' => $user->token];
        $body = [
            'phone_number' => '021998781890',
            'email' => 'TEST@example.com',
            'location' => 'Madureira',
            'car_owner' => true,
            'car_model' => 'Fiat Uno',
            'car_color' => 'azul',
            'car_plate' => 'ABC-1234',
            'profile_pic_url' => 'http://example.com/image.jpg',
            'facebook_id' => 'facebookid123456',
        ];

        $response = $this->json('PUT', 'user', $body, $headers);
        $response->assertStatus(200);

        $user = $user->fresh();
        $this->assertEquals($body['phone_number'], $user->phone_number);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals($body['location'], $user->location);
        $this->assertEquals($body['car_owner'], $user->car_owner);
        $this->assertEquals($body['car_model'], $user->car_model);
        $this->assertEquals($body['car_plate'], $user->car_plate);
        $this->assertEquals($body['profile_pic_url'], $user->profile_pic_url);
        $this->assertEquals($body['facebook_id'], $user->face_id);
    }

    /**
     * @test
     */
    public function shouldUpdateUserProfile()
    {
        $user = $this->someUser();
        $headers = ['token' => $user->token];
        $body = [
            'phone_number' => '021998781890',
            'email' => 'TEST@example.com',
            'location' => 'Madureira',
            'car_owner' => true,
            'car_model' => 'Fiat Uno',
            'car_color' => 'azul',
            'car_plate' => 'ABC-1234',
            'profile_pic_url' => 'http://example.com/image.jpg',
            'facebook_id' => 'facebookid123456',
        ];

        $response = $this->json('PUT', 'api/v1/users/' . $user->id, $body, $headers);
        $response->assertStatus(200);

        $user = $user->fresh();
        $this->assertEquals($body['phone_number'], $user->phone_number);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals($body['location'], $user->location);
        $this->assertEquals($body['car_owner'], $user->car_owner);
        $this->assertEquals($body['car_model'], $user->car_model);
        $this->assertEquals($body['car_plate'], $user->car_plate);
        $this->assertEquals($body['profile_pic_url'], $user->profile_pic_url);
        $this->assertEquals($body['facebook_id'], $user->face_id);
    }

    /**
     * @test
     */
    public function shouldNotUpdateProtectedFields()
    {
        $user = $this->someUser();
        $previousName = $user->name;
        $previousToken = $user->token;
        $previousInstitutionID = $user->id_ufrj;
        $previousCourse = $user->course;
        $previousProfile = $user->profile;

        $body = [
            'name' => 'New Name',
            'token' => 'newtoken',
            'id_ufrj' => 'newid',
            'institution_id' => null,
            'course' => 'newcourse',
            'profile' => 'newprofile',
        ];

        $response = $this->json('PUT', 'api/v1/users/' . $user->id, $body, ['token' => $user->token]);
        $response->assertStatus(200);

        $user = $user->fresh();
        $this->assertEquals($previousName, $user->name);
        $this->assertEquals($previousToken, $user->token);
        $this->assertEquals($previousInstitutionID, $user->id_ufrj);
        $this->assertEquals($previousCourse, $user->course);
        $this->assertEquals($previousProfile, $user->profile);
    }

    /**
     * @test
     */
    public function shouldNotUpdateOtherUser()
    {
        $user = $this->someUser();
        $otherUser = $this->someUser();

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

        $response = $this->json('PUT', 'api/v1/users/' . $otherUser->id, $body, [ 'token' => $user->token ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function shouldReturnRidesDividedByCategory()
    {
        $user = $this->someUser();

        $offeredRide = factory(Ride::class, 'next')->create()->fresh();
        $offeredRide->users()->attach($user, ['status' => 'driver']);

        $activeRide = factory(Ride::class, 'next')->create()->fresh();
        $activeRideDriver = $this->someUser();
        $activeRide->users()->attach($activeRideDriver, ['status' => 'driver']);
        $activeRide->users()->attach($user, ['status' => 'accepted']);

        $pendingRide = factory(Ride::class, 'next')->create()->fresh();
        $pendingRideDriver = $this->someUser();
        $pendingRide->users()->attach($pendingRideDriver, ['status' => 'driver']);
        $pendingRide->users()->attach($user, ['status' => 'pending']);

        $response = $this->json('GET', 'api/v1/users/' . $user->id . '/rides', [], ['token' => $user->token]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'pending_rides' => [
                [
                    'id' => $pendingRide->id,
                    'myzone' => $pendingRide->myzone,
                    'neighborhood' => $pendingRide->neighborhood,
                    'going' => $pendingRide->going,
                    'place' => $pendingRide->place,
                    'route' => $pendingRide->route,
                    'routine_id' => $pendingRide->routine_id,
                    'hub' => $pendingRide->hub,
                    'slots' => $pendingRide->slots,
                    'mytime' => $pendingRide->date->format('H:i:s'),
                    'mydate' => $pendingRide->date->format('Y-m-d'),
                    'description' => $pendingRide->description,
                    'week_days' => $pendingRide->week_days,
                    'repeats_until' => $pendingRide->repeats_until,
                    'driver' => $pendingRideDriver->toArray(),
                    'riders' => []
                ],
            ],
            'active_rides' => [
                [
                    'id' => $activeRide->id,
                    'myzone' => $activeRide->myzone,
                    'neighborhood' => $activeRide->neighborhood,
                    'going' => $activeRide->going,
                    'place' => $activeRide->place,
                    'route' => $activeRide->route,
                    'routine_id' => $activeRide->routine_id,
                    'hub' => $activeRide->hub,
                    'slots' => $activeRide->slots,
                    'mytime' => $activeRide->date->format('H:i:s'),
                    'mydate' => $activeRide->date->format('Y-m-d'),
                    'description' => $activeRide->description,
                    'week_days' => $activeRide->week_days,
                    'repeats_until' => $activeRide->repeats_until,
                    'driver' => $activeRideDriver->toArray(),
                    'riders' => [$user->toArray()]
                ],
            ],
            'offered_rides' => [
                [
                    'id' => $offeredRide->id,
                    'myzone' => $offeredRide->myzone,
                    'neighborhood' => $offeredRide->neighborhood,
                    'going' => $offeredRide->going,
                    'place' => $offeredRide->place,
                    'route' => $offeredRide->route,
                    'routine_id' => $offeredRide->routine_id,
                    'hub' => $offeredRide->hub,
                    'slots' => $offeredRide->slots,
                    'mytime' => $offeredRide->date->format('H:i:s'),
                    'mydate' => $offeredRide->date->format('Y-m-d'),
                    'description' => $offeredRide->description,
                    'week_days' => $offeredRide->week_days,
                    'repeats_until' => $offeredRide->repeats_until,
                    'driver' => $user->toArray(),
                    'riders' => []
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnRidesWithoutDuplicates()
    {
        $user = $this->someUser();

        // active ride that is both active and offered
        $activeRide = factory(Ride::class, 'next')->create()->fresh();
        $activeRideRider = $this->someUser();
        $activeRide->users()->attach($user, ['status' => 'driver']);
        $activeRide->users()->attach($activeRideRider, ['status' => 'accepted']);

        $pendingRide = factory(Ride::class, 'next')->create()->fresh();
        $pendingRide->users()->attach($this->someUser(), ['status' => 'driver']);
        $pendingRide->users()->attach($user, ['status' => 'pending']);

        $response = $this->json('GET', 'api/v1/users/' . $user->id . '/rides', [], ['token' => $user->token]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'pending_rides' => [
                [
                    'id' => $pendingRide->id,
                    'myzone' => $pendingRide->myzone,
                    'neighborhood' => $pendingRide->neighborhood,
                    'going' => $pendingRide->going,
                    'place' => $pendingRide->place,
                    'route' => $pendingRide->route,
                    'routine_id' => $pendingRide->routine_id,
                    'hub' => $pendingRide->hub,
                    'slots' => $pendingRide->slots,
                    'mytime' => $pendingRide->date->format('H:i:s'),
                    'mydate' => $pendingRide->date->format('Y-m-d'),
                    'description' => $pendingRide->description,
                    'week_days' => $pendingRide->week_days,
                    'repeats_until' => $pendingRide->repeats_until,
                    'driver' => $pendingRide->driver()->toArray(),
                    'riders' => []
                ],
            ],
            'active_rides' => [
                [
                    'id' => $activeRide->id,
                    'myzone' => $activeRide->myzone,
                    'neighborhood' => $activeRide->neighborhood,
                    'going' => $activeRide->going,
                    'place' => $activeRide->place,
                    'route' => $activeRide->route,
                    'routine_id' => $activeRide->routine_id,
                    'hub' => $activeRide->hub,
                    'slots' => $activeRide->slots,
                    'mytime' => $activeRide->date->format('H:i:s'),
                    'mydate' => $activeRide->date->format('Y-m-d'),
                    'description' => $activeRide->description,
                    'week_days' => $activeRide->week_days,
                    'repeats_until' => $activeRide->repeats_until,
                    'driver' => $user->toArray(),
                    'riders' => [$activeRideRider->toArray()]
                ],
            ],
            'offered_rides' => [
            ],
        ]);
    }

    /**
     * @test
     */
    public function shouldNotReturnRidesFromOtherUser()
    {
        $user = $this->someUser();
        $user2 = $this->someUser();

        $response = $this->json('GET', 'api/v1/users/' . $user2->id . '/rides', [], [
            'token' => $user->token
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function shouldReturnOfferedRides()
    {
        $user = $this->someUser();

        // add unfinished rides, which should be returned
        $ride = factory(Ride::class, 'next')->create(['done' => false])->fresh();
        $ride->users()->attach($user, ['status' => 'driver']);
        $rider = $this->someUser();
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
                    'driver' => $user->toArray(),
                    'riders' => [
                        $rider->toArray()
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function shouldNotReturnOfferedRidesFromOtherUser()
    {
        $user = $this->someUser();
        $user2 = $this->someUser();

        $response = $this->json('GET', 'user/' . $user2->id . '/offeredRides', [], [
            'token' => $user->token
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function shouldReturnPendingRides()
    {
        $user = $this->someUser();
        $driver = $this->someUser();

        $ride = $this->createPendingRideForUser($driver, $user);

        $response = $this->json('GET', 'user/' . $user->id . '/pendingRides', [], [
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
                    'driver' => $driver->toArray(),
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function shouldFailWhenGettingPendingRidesForOtherUser()
    {
        $user = $this->someUser();
        $user2 = $this->someUser();

        $response = $this->json('GET', 'user/' . $user2->id . '/pendingRides', [], [
            'token' => $user->token
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function shouldSaveFacebookID()
    {
        $user = $this->someUser();
        $headers = ['token' => $user->token];
        $newId = 'new-facebook-id';

        $response = $this->json('PUT', 'user/saveFaceId', [
            'id' => $newId
        ], $headers);

        $response->assertStatus(200);

        $savedUser = $user->fresh();
        $this->assertEquals($newId, $savedUser->face_id);
    }

    /**
     * @test
     */
    public function shouldSaveProfilePictureURL()
    {
        $user = $this->someUser();
        $headers = ['token' => $user->token];
        $newURL = 'https://example.com/new-profile-picture-url.jpg';

        $response = $this->json('PUT', 'user/saveProfilePicUrl', [
            'url' => $newURL
        ], $headers);

        $response->assertStatus(200);
        $this->assertEquals($newURL, $user->fresh()->profile_pic_url);
    }

    /**
     * @test
     */
    public function shouldReturnRideHistoryCount()
    {
        $user = $this->someUser();
        $otherUser = $this->someUser();
        $offeredRide = factory(Ride::class)->create(['done' => true]);
        $offeredRide->users()->attach($otherUser, ['status' => 'driver']);
        $takenRide1 = factory(Ride::class)->create(['done' => true]);
        $takenRide1->users()->attach($otherUser, ['status' => 'accepted']);
        $takenRide2 = factory(Ride::class)->create(['done' => true]);
        $takenRide2->users()->attach($otherUser, ['status' => 'accepted']);

        $response = $this->json('GET', 'api/v1/users/' . $otherUser->id . '/rides/history', [], ['token' => $user->token]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'offered_rides_count' => 1,
            'taken_rides_count' => 2,
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnDetailedRideHistoryForAuthenticatedUser()
    {
        $user = $this->someUser();
        $otherUser = $this->someUser();

        $offeredRide = factory(Ride::class)->create(['done' => true])->fresh();
        $offeredRide->users()->attach($user, ['status' => 'driver']);
        $takenRide1 = factory(Ride::class)->create(['done' => true])->fresh();
        $takenRide1->users()->attach($otherUser, ['status' => 'driver']);
        $takenRide1->users()->attach($user, ['status' => 'accepted']);
        $takenRide2 = factory(Ride::class)->create(['done' => false])->fresh();
        $takenRide2->users()->attach($user, ['status' => 'accepted']);

        $response = $this->json('GET', 'api/v1/users/' . $user->id . '/rides/history', [], ['token' => $user->token]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'offered_rides_count' => 1,
            'taken_rides_count' => 1,
            'rides' => [
                [
                    'id' => $offeredRide->id,
                    'myzone' => $offeredRide->myzone,
                    'neighborhood' => $offeredRide->neighborhood,
                    'going' => $offeredRide->going,
                    'place' => $offeredRide->place,
                    'route' => $offeredRide->route,
                    'routine_id' => $offeredRide->routine_id,
                    'hub' => $offeredRide->hub,
                    'slots' => $offeredRide->slots,
                    'mytime' => $offeredRide->date->format('H:i:s'),
                    'mydate' => $offeredRide->date->format('Y-m-d'),
                    'description' => $offeredRide->description,
                    'week_days' => $offeredRide->week_days,
                    'repeats_until' => $offeredRide->repeats_until,
                    'driver' => $user->toArray(),
                    'riders' => [],
                ],
                [
                    'id' => $takenRide1->id,
                    'myzone' => $takenRide1->myzone,
                    'neighborhood' => $takenRide1->neighborhood,
                    'going' => $takenRide1->going,
                    'place' => $takenRide1->place,
                    'route' => $takenRide1->route,
                    'routine_id' => $takenRide1->routine_id,
                    'hub' => $takenRide1->hub,
                    'slots' => $takenRide1->slots,
                    'mytime' => $takenRide1->date->format('H:i:s'),
                    'mydate' => $takenRide1->date->format('Y-m-d'),
                    'description' => $takenRide1->description,
                    'week_days' => $takenRide1->week_days,
                    'repeats_until' => $takenRide1->repeats_until,
                    'driver' => $otherUser->toArray(),
                    'riders' => [
                        $user->toArray(),
                    ],
                ],
            ],
        ]);
    }

    private function someUser()
    {
        return factory(User::class)->create()->fresh();
    }

    private function institutionAuthorizationHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->institution->id . ':' . $this->institution->password)
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

    private function createPendingRideForUser($driver, $user)
    {
        $ride = factory(Ride::class, 'next')->create(['done' => false])->fresh();
        $ride->users()->attach($driver, ['status' => 'driver']);
        $ride->users()->attach($user, ['status' => 'pending']);
        return $ride;
    }
}
