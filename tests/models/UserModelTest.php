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

        $ride = $this->createRide();
        $ride->users()->attach($rider, ['status' => 'accepted']);

        $activeRides = $this->driver->activeRides()->get();
        $this->assertContainsOnly($ride, $activeRides);

        $activeRides = $rider->activeRides()->get();
        $this->assertContainsOnly($ride, $activeRides);
    }

    public function testActiveDoesNotReturnFinishedRides()
    {
        $ride = $this->createRide(['done' => true]);
        $ride->users()->attach($this->driver, ['status' => 'driver']);

        $activeRides = $this->driver->activeRides()->get();
        $this->assertEmpty($activeRides);
    }

    public function testReturnsOfferedRides()
    {
        $otherUser = factory(User::class)->create();

        $ride1 = $this->createRide();
        $ride2 = $this->createRide();
        $ride2->users()->attach($otherUser, ['status' => 'driver']);

        $offeredRides = $this->driver->offeredRides()->get();
        $this->assertContainsOnly($ride1, $offeredRides);
    }

    public function testReturnsPendingRides()
    {
        $rider = factory(User::class)->create();
        $otherRider = factory(User::class)->create();

        $ride1 = $this->createRide();
        $ride1->users()->attach($rider, ['status' => 'pending']);

        $ride2 = $this->createRide();
        $ride2->users()->attach($rider, ['status' => 'accepted']);
        $ride2->users()->attach($otherRider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertContainsOnly($ride1, $pendingRides);
    }

    public function testPendingRidesDoesNotReturnFinishedRides()
    {
        $rider = factory(User::class)->create();

        $ride1 = $this->createRide(['done' => true]);
        $ride1->users()->attach($rider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertEmpty($pendingRides);
    }

    public function testPendingRidesDoesNotReturnPastRides()
    {
        $rider = factory(User::class)->create();

        $ride = $this->createRide(['done' => false, 'date' => Carbon::createFromDate(1970, 1, 1)]);
        $ride->users()->attach($rider, ['status' => 'pending']);

        $pendingRides = $rider->pendingRides()->get();
        $this->assertEmpty($pendingRides);
    }

    private function createRide($attributes = [])
    {
        $ride = factory(Ride::class, 'next')->create($attributes)->fresh();
        $ride->users()->attach($this->driver, ['status' => 'driver']);
        return $ride;
    }
}
