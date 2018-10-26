<?php

namespace Tests;

use Carbon\Carbon;
use Caronae\Models\Institution;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RideModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_return_driver()
    {
        $ride = factory(Ride::class)->create();

        $user = factory(User::class)->create();
        $ride->users()->attach($user, ['status' => 'driver']);

        $this->assertEquals($user->id, $ride->driver()->id);
    }

    /** @test */
    public function should_return_drivers_institution()
    {
        $ride = factory(Ride::class)->create();

        $user = factory(User::class)->create();
        $ride->users()->attach($user, ['status' => 'driver']);

        $this->assertEquals($user->institution, $ride->institution);
    }

    /** @test */
    public function should_only_return_accepted_users()
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

        $riders = $ride->riders;
        $this->assertCount(1, $riders);
        $this->assertEquals($accepted->id, $riders[0]->id);
    }

    /** @test */
    public function should_return_pending_users()
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

        $requests = $ride->requests;
        $this->assertCount(1, $requests);
        $this->assertTrue($requests->contains($pending));
    }

    /** @test */
    public function should_return_title_with_neighborhood_first_when_going()
    {
        $ride = factory(Ride::class)->create([
            'going' => true,
            'neighborhood' => 'Ipanema',
            'hub' => 'CCS',
            'date' => Carbon::createFromDate(2017, 01, 29)
        ]);
        $this->assertEquals('Ipanema → CCS | 29/01', $ride->title);
    }

    /** @test */
    public function should_return_title_with_hub_first_when_returning()
    {
        $ride = factory(Ride::class)->create([
            'going' => false,
            'neighborhood' => 'Ipanema',
            'hub' => 'CCS',
            'date' => Carbon::createFromDate(2017, 01, 29)
        ]);
        $this->assertEquals('CCS → Ipanema | 29/01', $ride->title);
    }

    /** @test */
    public function should_calculate_available_slots()
    {
        $ride = factory(Ride::class)->create(['slots' => 2])->fresh();
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'driver']);
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'accepted']);

        $this->assertEquals(1, $ride->availableSlots());
    }

    /**
     * @test
     */
    public function should_validate_if_around_a_similar_date()
    {
        $ride = factory(Ride::class)->create(['date' => Carbon::parse('2017-12-03 08:00')])->fresh();
        $date = Carbon::parse('2017-12-03 08:15');

        $this->assertTrue($ride->isAroundDate($date));
    }

    /**
     * @test
     */
    public function should_validate_if_around_a_distant_date()
    {
        $ride = factory(Ride::class)->create(['date' => Carbon::parse('2017-12-03 08:00')])->fresh();
        $date = Carbon::parse('2017-12-03 10:00');

        $this->assertFalse($ride->isAroundDate($date));
    }

    /** @test */
    public function should_return_rides_with_available_slots()
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

    /** @test */
    public function should_return_rides_in_the_future()
    {
        $ride = factory(Ride::class, 'next')->create()->fresh();
        $ride->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $rideOld = factory(Ride::class)->create(['date' => '1990-01-01 00:00:00']);
        $rideOld->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $results = Ride::inTheFuture()->get();
        $this->assertTrue($results->contains($ride));
        $this->assertFalse($results->contains($rideOld));
    }

    /** @test */
    public function should_filter_by_neighborhoods()
    {
        $ride1 = factory(Ride::class, 'next')->create(['neighborhood' => 'Ipanema'])->fresh();
        $ride1->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $ride2 = factory(Ride::class, 'next')->create(['neighborhood' => 'Niterói'])->fresh();
        $ride2->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $ride3 = factory(Ride::class, 'next')->create(['neighborhood' => 'Leblon'])->fresh();
        $ride3->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $results = Ride::withFilters(['neighborhoods' => ['Ipanema', 'Leblon']])->get();
        $this->assertTrue($results->contains($ride1));
        $this->assertFalse($results->contains($ride2));
        $this->assertTrue($results->contains($ride3));
    }

    /** @test */
    public function should_filter_by_hubs()
    {
        $ride1 = factory(Ride::class, 'next')->create(['hub' => 'FND'])->fresh();
        $ride1->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $ride2 = factory(Ride::class, 'next')->create(['hub' => 'CT: Bloco A'])->fresh();
        $ride2->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $ride3 = factory(Ride::class, 'next')->create(['hub' => 'CT: Bloco H'])->fresh();
        $ride3->users()->attach(factory(User::class)->create(), ['status' => 'driver']);

        $results = Ride::withFilters(['hubs' => ['CT: Bloco A', 'CT: Bloco H']])->get();
        $this->assertFalse($results->contains($ride1));
        $this->assertTrue($results->contains($ride2));
        $this->assertTrue($results->contains($ride3));
    }

    /** @test */
    public function should_return_right_directions_when_going()
    {
        $ride = factory(Ride::class)->create(['going' => true, 'hub' => 'Hub', 'neighborhood' => 'Bairro'])->fresh();

        $this->assertEquals('Hub', $ride->origin);
        $this->assertEquals('Bairro', $ride->destination);
    }

    /** @test */
    public function should_return_right_directions_when_returning()
    {
        $ride = factory(Ride::class)->create(['going' => false, 'hub' => 'Hub', 'neighborhood' => 'Bairro'])->fresh();

        $this->assertEquals('Bairro', $ride->origin);
        $this->assertEquals('Hub', $ride->destination);
    }

    /** @test */
    public function should_filter_by_institution()
    {
        $institutionA = factory(Institution::class)->create();
        $userA = factory(User::class)->create(['institution_id' => $institutionA->id]);
        $rideA = factory(Ride::class)->create();
        $rideA->users()->attach($userA, ['status' => 'driver']);

        $institutionB = factory(Institution::class)->create();
        $userB = factory(User::class)->create(['institution_id' => $institutionB->id]);
        $rideB = factory(Ride::class)->create();
        $rideB->users()->attach($userB, ['status' => 'driver']);

        $rides = Ride::withInstitution($institutionA)->get();
        $this->assertTrue($rides->contains($rideA));
        $this->assertFalse($rides->contains($rideB));
    }
}
