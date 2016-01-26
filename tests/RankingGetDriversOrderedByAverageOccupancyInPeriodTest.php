<?php

use App\RankingService;
use App\Ride;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class RankingGetDriversOrderedByAverageOccupancyInPeriodTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @before
     */
    public function cleanDatabase()
    {
        $this->beginDatabaseTransaction();

        DB::table('ride_user')->delete();
        DB::table('users')->delete();
        DB::table('rides')->delete();

        Model::unguard();
    }

    public function createRide($user, $rideAttr, $amount){
        $ride = factory(Ride::class)->create($rideAttr);
        $user->rides()->save($ride, ['status' => 'driver']);
        factory(User::class, $amount)->create()->each(function($user) use($ride) {
            $user->rides()->save($ride, ['status' => 'accepted']);
        });
    }

    public function testAverageHaveCorrectValue()
    {
        $user = factory(User::class)->create();

        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 2);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->moda == 3);
        $this->assertTrue(round($users[0]->media, 1) == 2.7);
    }

    public function testModeHaveCorrectValue()
    {
        $user = factory(User::class)->create();

        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 8);
        $this->createRide($user, ['done' => true], 4);
        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 2);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 5);
        $this->assertTrue($users[0]->moda == 3);
        $this->assertTrue(round($users[0]->media, 1) == 4);
    }

    public function testOnlyConsiderDoneRides()
    {
        $user = factory(User::class)->create();

        $this->createRide($user, ['done' => true], 4);
        $this->createRide($user, ['done' => true], 4);
        $this->createRide($user, ['done' => false], 3);
        $this->createRide($user, ['done' => true], 2);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->moda == 4);
        $this->assertTrue(round($users[0]->media, 1) == 3.3);
    }

    public function testOnlyConsiderInsidePeriod()
    {
        $user = factory(User::class)->create();
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-08'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-08'], 5);

        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 2);

        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-12'], 3);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::createFromDate(2015, 1, 9),
            Carbon::createFromDate(2015, 1, 11));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->moda == 3);
        $this->assertTrue(round($users[0]->media, 1) == 2.7);
    }

    public function testOnlyConsiderInsidePeriodInclusive()
    {
        $user = factory(User::class)->create();
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-08'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-08'], 4);

        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 2);

        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-12'], 3);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::createFromDate(2015, 1, 8),
            Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 5);
        $this->assertTrue($users[0]->moda == 3);
        $this->assertTrue(round($users[0]->media, 1) == 3);
    }

    public function testCanSelectOneDayPeriod()
    {
        $user = factory(User::class)->create();
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-08'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-08'], 4);

        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 3);
        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-10'], 2);

        $this->createRide($user, ['done' => true, 'mydate' => '2015-01-12'], 3);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::createFromDate(2015, 1, 10),
            Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->moda == 3);
        $this->assertTrue(round($users[0]->media, 1) == 2.7);
    }

    public function testOrderCorrectly()
    {
        $user2 = factory(User::class)->create();

        $this->createRide($user2, ['done' => true], 3);
        $this->createRide($user2, ['done' => true], 3);
        $this->createRide($user2, ['done' => true], 2);

        $user = factory(User::class)->create();

        $this->createRide($user, ['done' => true], 4);
        $this->createRide($user, ['done' => true], 4);
        $this->createRide($user, ['done' => true], 2);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 2);

        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->moda == 4);
        $this->assertTrue(round($users[0]->media, 1) == 3.3);

        $this->assertTrue($users[1]->caronas == 3);
        $this->assertTrue($users[1]->moda == 3);
        $this->assertTrue(round($users[1]->media, 1) == 2.7);
    }

    public function testConsiderOnlyActiveUsers()
    {
        $user2 = factory(User::class)->create(['deleted_at' => '2015-01-23']);

        $this->createRide($user2, ['done' => true], 3);
        $this->createRide($user2, ['done' => true], 3);
        $this->createRide($user2, ['done' => true], 2);

        $user = factory(User::class)->create();

        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 2);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->moda == 3);
        $this->assertTrue(round($users[0]->media, 1) == 2.7);
    }

    public function testIgnoreUserThatIsNotADriver(){
        factory(User::class)->create(['car_owner' => false]);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 0);
    }

    public function testNotADriverWithRideAppears(){
        $user = factory(User::class)->create(['car_owner' => false]);

        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 3);
        $this->createRide($user, ['done' => true], 2);

        $users = with(new RankingService)->getDriversOrderedByAverageOccupancyInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
    }

}
