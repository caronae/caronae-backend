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
    public function should_return_first_name()
    {
        $user = factory(User::class)->create(['name' => 'Fulana Silva']);

        $this->assertEquals('Fulana', $user->firstName);
    }

    /** @test */
    public function should_ban_user() {
        $user = factory(User::class)->create();

        $user->banish();

        $this->assertTrue($user->banned);
    }

    /** @test */
    public function should_delete_unfinished_rides_when_user_is_banned() {
        $rideFinished = $this->createRideAsDriver(['done' => true]);
        $this->createRideAsDriver(['done' => false]);

        $this->driver->banish();

        $rides = $this->driver->rides()->get();
        $this->assertCount(1, $rides);
        $this->assertEquals($rideFinished->id, $rides[0]->id);
    }

    /** @test */
    public function should_delete_requests_for_unfinished_rides_when_user_is_banned() {
        $user = factory(User::class)->create();

        $rideFinished = $this->createRide(['done' => true]);
        $rideFinished->users()->attach($user, ['status' => 'accepted']);
        $rideNotFinished = $this->createRide(['done' => false]);
        $rideNotFinished->users()->attach($user, ['status' => 'accepted']);

        $user->banish();

        $rides = $user->rides()->get();
        $this->assertCount(1, $rides);
        $this->assertEquals($rideFinished->id, $rides[0]->id);
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

    /** @test */
    public function should_return_ios_users_with_app_versions_prior_to_1_5_0()
    {
        $userOld = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '1.4.0']);
        $userVeryOld = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '1.0.1']);

        $users = User::withOldAppVersion()->get();

        $this->assertTrue($users->contains($userOld));
        $this->assertTrue($users->contains($userVeryOld));
    }

    /** @test */
    public function should_not_return_ios_users_with_app_versions_equal_or_later_than_1_5_0()
    {
        $userNew = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '1.5.0']);
        $userVeryNew = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '2.0.0']);

        $users = User::withOldAppVersion()->get();

        $this->assertFalse($users->contains($userNew));
        $this->assertFalse($users->contains($userVeryNew));
    }

    /** @test */
    public function should_return_android_users_with_app_versions_prior_to_3_0_3()
    {
        $userOld = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '3.0.2']);
        $userVeryOld = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '2.2.6']);
        $userVeryVeryOld = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '2.1.3']);

        $users = User::withOldAppVersion()->get();
        $this->assertTrue($users->contains($userOld));
        $this->assertTrue($users->contains($userVeryOld));
        $this->assertTrue($users->contains($userVeryVeryOld));
    }

    /** @test */
    public function should_not_return_android_users_with_app_versions_equal_or_later_then_3_0_3()
    {
        $userNew = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '3.0.3']);
        $userVeryNew = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '4.0.0']);

        $users = User::withOldAppVersion()->get();

        $this->assertFalse($users->contains($userNew));
        $this->assertFalse($users->contains($userVeryNew));
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
