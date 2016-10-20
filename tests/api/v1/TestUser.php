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
        // var_dump($user);
        $user = User::find($user->id);
        // add unfinished rides
        $rides = [];
        // $rides = factory(Ride::class, 3)->create(['done' => false])->each(function($ride) use ($user) {
        //     $user->rides()->attach($ride, ['status' => 'driver']);
        // });
        // // add finished rides
        // factory(Ride::class, 3)->create(['done' => true])->each(function($ride) use ($user) {
        //     $user->rides()->attach($ride, ['status' => 'driver']);
        // });

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
            'id_ufrj' => 'l4ki4h23i',
            'token' => 'cru32h4i3r'
        ]);
        $response->assertResponseStatus(403);
        $response->seeJsonEquals([
            'error' => 'User not found with provided credentials.'
        ]);
    }
}
