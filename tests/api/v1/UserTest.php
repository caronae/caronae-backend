<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Ride;
use App\Repositories\SigaInterface;

class UserTest extends TestCase
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

    public function testSignUpIntranet()
    {
        $id_ufrj = str_random(10);
        $token = str_random(6);

        // Mock Siga interface
        App::singleton(SigaInterface::class, function($app) use ($id_ufrj) {
            $mockProfile = new stdClass;
            $mockProfile->nome = 'FULANO SILVA';
            $mockProfile->nomeCurso = 'Engenharia';
            $mockProfile->alunoServidor = '0';
            $mockProfile->nivel = 'Graduação';
            $mockProfile->urlFoto = '146.164.2.117:8090/image.jpg';
            $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
            $sigaRepositoryMock->shouldReceive('getProfileById')->once()->with($id_ufrj)->andReturn($mockProfile);
            return $sigaRepositoryMock;
        });

        $response = $this->json('GET', "user/signup/intranet/$id_ufrj/$token");
        $response->assertResponseOk();
        $response->seeJsonContains([
            'name' => 'Fulano Silva',
            'course' => 'Engenharia',
            'profile' => 'Graduação',
            'profile_pic_url' => 'https://api.caronae.ufrj.br/user/intranetPhoto/image.jpg'
        ]);
        // $response->seeJsonStructure([
        //     '*' => ['id', 'created_at']
        // ]);
    }

    public function testLoginWithValidUser()
    {
        // create user with some rides
        $user = factory(User::class)->create();
        $user = User::find($user->id);

        // add unfinished rides, which should be returned
        $rideIds = [];
        $rides = factory(Ride::class, 3)->create(['done' => false])->each(function($ride) use ($user, &$rideIds) {
            $user->rides()->attach($ride, ['status' => 'driver']);
            $rideIds[] = $ride->id;
        });
        $rides = Ride::findMany($rideIds);

        // add finished rides, which shouldn't be returned
        factory(Ride::class, 3)->create(['done' => true])->each(function($ride) use ($user) {
            $user->rides()->attach($ride, ['status' => 'driver']);
        });

        $response = $this->json('POST', 'user/login', [
            'id_ufrj' => $user->id_ufrj,
            'token' => $user->token
        ]);

        $response->assertResponseOk();
        $response->seeJsonEquals([
            'user' => $user->toArray(),
            'rides' => $rides->toArray()
        ]);
    }

    public function testLoginWithInvalidUser()
    {
        $response = $this->json('POST', 'user/login', [
            'id_ufrj' => str_random(11),
            'token' => str_random(6)
        ]);
        $response->assertResponseStatus(403);
        $response->seeJsonEquals([
            'error' => 'User not found with provided credentials.'
        ]);
    }

    public function testUpdate()
    {
        $faker = Faker\Factory::create();
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];

        $user->face_id = NULL;
        $user->profile = $faker->titleMale;
        $user->course = $faker->company;
        $user->phone_number = $faker->regexify('[0-9]{10-11}');
        $user->email = $faker->email;
        $user->location = $faker->city;
        $user->car_owner = !$user->car_owner;
        $user->car_model = $user->car_owner ? $faker->company : NULL;
        $user->car_color = $user->car_owner ? $faker->colorName : NULL;
        $user->car_plate = $user->car_owner ? 'ABC-1234' : NULL;
        $user->profile_pic_url = $faker->url;

        $response = $this->json('PUT', 'user', $user->toArray(), $headers);
        $response->assertResponseOk();

        $savedUser = User::find($user->id);
        $this->assertEquals($user->toArray(), $savedUser->toArray());
    }

    public function testSaveGcmToken()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];
        $newToken = str_random(255);

        $response = $this->json('PUT', 'user/saveGcmToken', [
            'token' => $newToken
        ], $headers);
        $response->assertResponseOk();

        $savedUser = User::find($user->id);
        $this->assertEquals($newToken, $savedUser->gcm_token);
    }

    public function testSaveFacebookID()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];
        $newId = str_random(50);

        $response = $this->json('PUT', 'user/saveFaceId', [
            'id' => $newId
        ], $headers);
        $response->assertResponseOk();

        $savedUser = User::find($user->id);
        $this->assertEquals($newId, $savedUser->face_id);
    }

    public function testSaveProfilePictureURL()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];
        $newURL = Faker\Factory::create()->url;

        $response = $this->json('PUT', 'user/saveProfilePicUrl', [
            'url' => $newURL
        ], $headers);
        $response->assertResponseOk();

        $savedUser = User::find($user->id);
        $this->assertEquals($newURL, $savedUser->profile_pic_url);
    }

    public function testGetMutualFriends()
    {

    }

    public function testLoadIntranetPhoto()
    {

    }

    public function testGetIntranetPhotoUrl()
    {
        $user = factory(User::class)->create();
        $headers = ['token' => $user->token];

        // Mock Siga interface
        App::singleton(SigaInterface::class, function($app) use ($user) {
            $mockProfile = new stdClass;
            $mockProfile->urlFoto = '146.164.2.117:8090/image.jpg';
            $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
            $sigaRepositoryMock->shouldReceive('getProfileById')->once()->with($user->id_ufrj)->andReturn($mockProfile);
            return $sigaRepositoryMock;
        });

        $response = $this->json('GET', 'user/intranetPhotoUrl', [], $headers);
        $response->assertResponseOk();
        $response->seeJsonEquals([
            'url' => 'https://api.caronae.ufrj.br/user/intranetPhoto/image.jpg'
        ]);
    }
}
