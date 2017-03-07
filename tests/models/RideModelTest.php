<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Models\User;
use Caronae\Models\Ride;

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
        $ride = factory(Ride::class)->create();

        $user = factory(User::class)->create();
        $ride->users()->attach($user, ['status' => 'driver']);

        $this->assertEquals($user->id, $ride->driver()->id);
    }

    public function testGetRiders()
    {
        $ride = factory(Ride::class)->create();

        $driver = factory(User::class)->create();
        $ride->users()->attach($driver, ['status' => 'driver']);

        $pending = factory(User::class)->create();
        $ride->users()->attach($pending, ['status' => 'pending']);

        $rejected = factory(User::class)->create();
        $ride->users()->attach($rejected, ['status' => 'rejected']);

        $accepted = factory(User::class)->create();
        $ride->users()->attach($accepted, ['status' => 'accepted']);

        $this->assertEquals([$accepted->toArray()], $ride->riders()->toArray());
    }

    public function testTitleGoing()
    {
        $ride = factory(Ride::class)->create([
            'going' => true,
            'neighborhood' => 'Ipanema',
            'hub' => 'CCS',
            'date' => Carbon\Carbon::createFromDate(2017, 01, 29)
        ]);
        $this->assertEquals('Ipanema → CCS | 29/01', $ride->title);
    }

    public function testTitleReturning()
    {
        $ride = factory(Ride::class)->create([
            'going' => false,
            'neighborhood' => 'Ipanema',
            'hub' => 'CCS',
            'date' => Carbon\Carbon::createFromDate(2017, 01, 29)
        ]);
        $this->assertEquals('CCS → Ipanema | 29/01', $ride->title);
    }

}
