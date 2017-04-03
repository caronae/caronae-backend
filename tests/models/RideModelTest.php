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

    public function testScopeShouldReturnRidesWithAvailableSlots()
    {
        $ride = factory(Ride::class)->create([ 'slots' => 2 ])->fresh();
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'driver']);
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'accepted']);

        $rideFull = factory(Ride::class)->create([ 'slots' => 1 ])->fresh();
        $rideFull->users()->attach(factory(User::class)->create(), ['status' => 'driver']);
        $rideFull->users()->attach(factory(User::class)->create(), ['status' => 'accepted']);

        $rideFullWithPending = factory(Ride::class)->create([ 'slots' => 1 ])->fresh();
        $rideFullWithPending->users()->attach(factory(User::class)->create(), ['status' => 'driver']);
        $rideFullWithPending->users()->attach(factory(User::class)->create(), ['status' => 'pending']);

        $results = Ride::withAvailableSlots()->get();
        $this->assertTrue($results->contains($ride));
        $this->assertFalse($results->contains($rideFull));
        $this->assertFalse($results->contains($rideFullWithPending), 'Riders with pending status should take a slot.');
    }

    public function testScopeShouldReturnRidesInTheFuture() 
    {
        $ride = factory(Ride::class, 'next')->create()->fresh();
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $rideOld = factory(Ride::class)->create(['date' => '1990-01-01 00:00:00']);
        $rideOld->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $results = Ride::inTheFuture()->get();
        $this->assertTrue($results->contains($ride));
        $this->assertFalse($results->contains($rideOld));
    }

    public function testScopeShouldApplyFilters() 
    {
        $ride1 = factory(Ride::class, 'next')->create(['neighborhood' => 'Ipanema', 'hub' => 'CCMN: Fundos'])->fresh();
        $ride1->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $ride2 = factory(Ride::class, 'next')->create(['neighborhood' => 'Niterói', 'hub' => 'CT: Bloco A'])->fresh();
        $ride2->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $ride3 = factory(Ride::class, 'next')->create(['neighborhood' => 'Leblon', 'hub' => 'CT: Bloco H'])->fresh();
        $ride3->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $results = Ride::withFilters(['neighborhoods' => ['Ipanema', 'Leblon']])->get();
        $this->assertTrue($results->contains($ride1));
        $this->assertFalse($results->contains($ride2));
        $this->assertTrue($results->contains($ride3));

        $results = Ride::withFilters(['hub' => 'CT'])->get();
        $this->assertFalse($results->contains($ride1));
        $this->assertTrue($results->contains($ride2));
        $this->assertTrue($results->contains($ride3));
    }
}
