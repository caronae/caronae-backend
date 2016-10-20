<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Ride;

class TestUser extends TestCase
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

    public function testLoginWithValidUser()
    {
        // create user with some rides
        $user = factory(User::class)->create();
        $user = User::find($user->id);
        
        // add unfinished rides
        $rides = factory(Ride::class, 3)->create(['done' => false])->each(function($ride) use ($user) {
            $user->rides()->attach($ride, ['status' => 'driver']);
        });
        for ($i=0; $i<count($rides); $i++) {
            $rides[$i] = Ride::find($rides[$i]->id);
        }

        // add finished rides
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
            'rides' => $rides
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

        $response = $this->json('PUT', 'user', $user->toArray());
        $response->assertResponseStatus(200);

        $savedUser = User::find($user->id);
        $this->assertEquals($user->toArray(), $savedUser->toArray());
    }
}
