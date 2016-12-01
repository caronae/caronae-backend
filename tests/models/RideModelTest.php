<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Models\User;
use Caronae\Models\Ride;
use Caronae\Services\PushNotificationService;

class RideModelTest extends TestCase
{
    use DatabaseTransactions;

    /**
    * @before
    */
    public function cleanDatabase()
    {
        DB::table('ride_user')->delete();
        DB::table('users')->delete();
        DB::table('rides')->delete();
    }

    public function testGetDriver()
    {
        $user = factory(User::class)->create();

        $ride = factory(Ride::class)->create();
        $ride->users()->attach($user, ['status' => 'driver']);

        $this->assertEquals($user->id, $ride->driver()->id);
    }

}
