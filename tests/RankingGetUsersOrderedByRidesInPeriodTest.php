<?php

use Caronae\Services\RankingService;
use Caronae\Models\Ride;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Caronae\Models\User;

class RankingGetUsersOrderedByRidesInPeriodTest extends TestCase
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

    public function createRides($user, $rideAttrs){
        factory(User::class, count($rideAttrs))->create()->each(function($u, $i) use($user, $rideAttrs) {
            $ride = factory(Ride::class)->create($rideAttrs[$i]);
            $u->rides()->save($ride, ['status' => 'driver']);
            $user->rides()->save($ride, ['status' => 'accepted']);
        });
    }

    public function testCaronasHaveCorrectValue()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true], ['done' => true], ['done' => true]
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
    }

    public function testOnlyConsiderDoneRides()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true], ['done' => true], ['done' => false]
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
    }

    public function testOnlyConsiderInsidePeriod()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true, 'mydate' => '2015-01-08'], ['done' => true, 'mydate' => '2015-01-10'], ['done' => true, 'mydate' => '2015-01-12']
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 9), Carbon::createFromDate(2015, 1, 11));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
    }

    public function testOnlyConsiderInsidePeriodInclusive()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true, 'mydate' => '2015-01-09'], ['done' => true, 'mydate' => '2015-01-10'], ['done' => true, 'mydate' => '2015-01-12']
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 9), Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
    }

    public function testCanSelectOneDayPeriod()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true, 'mydate' => '2015-01-08'], ['done' => true, 'mydate' => '2015-01-10'], ['done' => true, 'mydate' => '2015-01-12']
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 10), Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
    }

    public function testOrderCorrectly()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true], ['done' => true], ['done' => true]
        ]);

        $user2 = factory(User::class)->create();

        $this->createRides($user2, [
            ['done' => true], ['done' => true], ['done' => true], ['done' => true]
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 2);
        $this->assertTrue($users[0]->caronas == 4);
        $this->assertTrue($users[1]->caronas == 3);
    }

    public function testConsiderOnlyActiveUsers()
    {
        $user = factory(User::class)->create(['deleted_at' => '2015-01-23']);

        $this->createRides($user, [
            ['done' => true], ['done' => true], ['done' => true]
        ]);

        $user2 = factory(User::class)->create();

        $this->createRides($user2, [
            ['done' => true], ['done' => true], ['done' => true], ['done' => true]
        ]);

        $users = with(new RankingService)->getUsersOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 4);
    }

}
