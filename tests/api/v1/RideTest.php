<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Ride;
use App\RideUser;

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

    public function testGetAll()
    {
        $user = $this->user;
        $rideIds = [];
        $rides = factory(Ride::class, 'next', 3)->create(['done' => false])->each(function($ride) use ($user, &$rideIds) {
            $ride->users()->attach($user, ['status' => 'driver']);
            $ride->driver = $user;
            unset($ride->done);
            $rideIds[] = $ride->id;
        });
        // $rides = Ride::findMany($rideIds);

        $response = $this->json('GET', "ride/all", [], $this->headers);
        $response->assertResponseOk();

        foreach ($rides as $ride) {
            // var_dump($ride->toArray());

            $response->seeJsonContains($ride->toArray());
        }
    }
}
