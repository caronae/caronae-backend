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

    /**
     * @test
     */
    public function shouldFindUserByInstitutionID()
    {
        $user = factory(User::class)->create(['id_ufrj' => '666'])->fresh();

        $this->assertEquals($user, User::findByInstitutionId('666'));
    }

    public function testActiveReturnsRidesWithAcceptedUsers()
    {
        $rider = factory(User::class)->create();

        $ride = $this->createRideAsDriver();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        $rides = $this->driver->activeRides()->get();
        $this->assertTrue($rides->contains($ride));

        $rides = $rider->activeRides()->get();
        $this->assertTrue($rides->contains($ride));
    }

    public function testActiveDoesNotReturnFinishedRides()
    {
        $ride = $this->createRideAsDriver(['done' => true]);
        $ride->users()->attach($this->driver, ['status' => 'driver']);

        $activeRides = $this->driver->activeRides()->get();
        $this->assertEmpty($activeRides);
    }

    public function testReturnsOfferedRides()
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
    public function shouldOnlyReturnAvailableRidesFromUser()
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
    public function shouldNotConsiderAvailableRidesThatAreFinished()
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
    public function shouldNotConsiderAvailableRidesThatAreInThePast()
    {
        $ride1 = $this->createRideAsDriver();
        $ride2 = $this->createRideAsDriver(['date' => Carbon::createFromDate(1970, 1, 1)]);

        $rides = $this->driver->availableRides()->get();
        $this->assertTrue($rides->contains($ride1));
        $this->assertFalse($rides->contains($ride2));
    }

    public function testReturnsPendingRides()
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

    public function testPendingRidesDoesNotReturnFinishedRides()
    {
        $rider = factory(User::class)->create();

        $ride1 = $this->createRideAsDriver(['done' => true]);
        $ride1->users()->attach($rider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertEmpty($pendingRides);
    }

    public function testPendingRidesDoesNotReturnPastRides()
    {
        $rider = factory(User::class)->create();

        $ride = $this->createRideAsDriver(['done' => false, 'date' => Carbon::createFromDate(1970, 1, 1)]);
        $ride->users()->attach($rider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertEmpty($pendingRides);
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
