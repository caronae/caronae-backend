<?php

namespace Tests;

use Carbon\Carbon;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;
    private $driver;

    public function setUp()
    {
        parent::setUp();
        $this->driver = factory(User::class)->create();
    }

    /** @test */
    public function should_find_user_by_institution_id()
    {
        $user = factory(User::class)->create(['id_ufrj' => '666'])->fresh();

        $this->assertEquals($user, User::findByInstitutionId('666'));
    }

    /** @test */
    public function should_return_rides_with_accepted_users()
    {
        $rider = factory(User::class)->create();

        $ride = $this->createRideAsDriver();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        $rides = $this->driver->activeRides()->get();
        $this->assertTrue($rides->contains($ride));

        $rides = $rider->activeRides()->get();

        $this->assertTrue($rides->contains($ride));
    }

    /** @test */
    public function should_not_return_finished_rides()
    {
        $this->createRideAsDriver(['done' => true]);

        $activeRides = $this->driver->activeRides()->get();

        $this->assertEmpty($activeRides);
    }

    /** @test */
    public function should_not_return_empty_rides()
    {
        $this->createRideAsDriver();

        $activeRides = $this->driver->activeRides()->get();

        $this->assertEmpty($activeRides);
    }

    /** @test */
    public function should_not_return_pending_or_refused_rides()
    {
        $otherUser = factory(User::class)->create();

        $ride1 = $this->createRide();
        $ride1->users()->attach($otherUser, ['status' => 'accepted']);
        $ride1->users()->attach($this->driver, ['status' => 'pending']);
        $ride2 = $this->createRide();
        $ride2->users()->attach($otherUser, ['status' => 'accepted']);
        $ride2->users()->attach($this->driver, ['status' => 'refused']);

        $activeRides = $this->driver->activeRides()->get();

        $this->assertEmpty($activeRides);
    }

    /** @test */
    public function should_return_offered_rides()
    {
        $otherUser = factory(User::class)->create();

        $ride1 = $this->createRideAsDriver();
        $ride2 = $this->createRide();
        $ride2->users()->attach($otherUser, ['status' => 'driver']);

        $rides = $this->driver->offeredRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
    }

    /**
     * @test
     */
    public function should_only_return_available_rides_from_user()
    {
        $otherUser = factory(User::class)->create();

        $ride1 = $this->createRideAsDriver();
        $ride2 = $this->createRide();
        $ride2->users()->attach($otherUser, ['status' => 'driver']);

        $rides = $this->driver->availableRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
    }

    /**
     * @test
     */
    public function should_not_consider_available_rides_that_are_finished()
    {
        $ride1 = $this->createRideAsDriver();
        $ride2 = $this->createRideAsDriver(['done' => true]);

        $rides = $this->driver->availableRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
    }

    /**
     * @test
     */
    public function should_not_consider_available_rides_that_are_in_the_past()
    {
        $ride1 = $this->createRideAsDriver();
        $ride2 = $this->createRideAsDriver(['date' => Carbon::createFromDate(1970, 1, 1)]);

        $rides = $this->driver->availableRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
    }

    /** @test */
    public function should_return_pending_rides()
    {
        $rider = factory(User::class)->create();
        $otherRider = factory(User::class)->create();

        $ride1 = $this->createRideAsDriver();
        $ride1->users()->attach($rider, ['status' => 'pending']);

        $ride2 = $this->createRideAsDriver();
        $ride2->users()->attach($rider, ['status' => 'accepted']);
        $ride2->users()->attach($otherRider, ['status' => 'pending']);

        $rides = $rider->pendingRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
    }

    /** @test */
    public function should_not_return_finished_rides_as_pending()
    {
        $rider = factory(User::class)->create();

        $ride1 = $this->createRideAsDriver(['done' => true]);
        $ride1->users()->attach($rider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertEmpty($pendingRides);
    }

    /** @test */
    public function should_not_return_past_rides_as_pending()
    {
        $rider = factory(User::class)->create();

        $ride = $this->createRideAsDriver(['done' => false, 'date' => Carbon::createFromDate(1970, 1, 1)]);
        $ride->users()->attach($rider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertEmpty($pendingRides);
    }

    /**
     * @test
     */
    public function should_return_accepted_rides()
    {
        $user = factory(User::class)->create();

        $ride1 = $this->createRide();
        $ride1->users()->attach($user, ['status' => 'accepted']);
        $ride2 = $this->createRide();
        $ride2->users()->attach($user, ['status' => 'pending']);
        $ride3 = $this->createRide();
        $ride3->users()->attach($user, ['status' => 'refused']);
        $ride4 = $this->createRide();
        $ride4->users()->attach($user, ['status' => 'driver']);

        $rides = $user->acceptedRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
        $this->assertFalse($rides->contains($ride3));
        $this->assertFalse($rides->contains($ride4));
    }

    private function createRideAsDriver($attributes = [])
    {
        $ride = $this->createRide($attributes);
        $ride->users()->attach($this->driver, ['status' => 'driver']);
        return $ride;
    }

    private function createRide($attributes = [])
    {
        return factory(Ride::class, 'next')->create($attributes)->fresh();
    }
}
