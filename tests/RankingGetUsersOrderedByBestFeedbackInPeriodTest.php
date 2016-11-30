<?php

use Caronae\Services\RankingService;
use Caronae\Models\Ride;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Caronae\Models\User;

class RankingGetUsersOrderedByBestFeedbackInPeriodTest extends TestCase
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

    public function createRideWithFeedback($user, $rideAttr, $feedbacks){
        $ride = factory(Ride::class)->create($rideAttr);
        $user->rides()->save($ride, ['status' => 'driver']);
        factory(User::class, count($feedbacks))->create()->each(function($user, $i) use($ride, $feedbacks) {
            $user->rides()->save($ride, ['status' => 'accepted','feedback' => $feedbacks[$i]]);
        });
    }

    public function testConsiderMoreThanOneDrive()
    {
        $user = factory(User::class)->create();

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
        $this->assertTrue($users[0]->caronistas == 6);
        $this->assertTrue($users[0]->feedback_positivo == 4);
        $this->assertTrue($users[0]->feedback_negativo == 2);
    }

    public function testOnlyConsiderDoneRides()
    {
        $user = factory(User::class)->create();

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad']);

        $this->createRideWithFeedback($user, ['done' => false], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 3);
        $this->assertTrue($users[0]->feedback_positivo == 2);
        $this->assertTrue($users[0]->feedback_negativo == 1);
    }

    public function testOnlyConsiderInsidePeriod()
    {
        $user = factory(User::class)->create();
        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-08'], ['good', 'good', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-10'], ['good', 'bad', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-12'], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::createFromDate(2015, 1, 9),
            Carbon::createFromDate(2015, 1, 11));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 3);
        $this->assertTrue($users[0]->feedback_positivo == 1);
        $this->assertTrue($users[0]->feedback_negativo == 2);
    }

    public function testOnlyConsiderInsidePeriodInclusive()
    {
        $user = factory(User::class)->create();
        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-09'], ['good', 'good', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-10'], ['good', 'bad', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-12'], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::createFromDate(2015, 1, 9),
            Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
        $this->assertTrue($users[0]->caronistas == 6);
        $this->assertTrue($users[0]->feedback_positivo == 3);
        $this->assertTrue($users[0]->feedback_negativo == 3);
    }

    public function testCanSelectOneDayPeriod()
    {
        $user = factory(User::class)->create();
        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-08'], ['good', 'good', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-10'], ['good', 'bad', 'bad']);

        $this->createRideWithFeedback($user, ['done' => true, 'mydate' => '2015-01-12'], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::createFromDate(2015, 1, 10),
            Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 3);
        $this->assertTrue($users[0]->feedback_positivo == 1);
        $this->assertTrue($users[0]->feedback_negativo == 2);
    }

    public function testOrderCorrectly()
    {
        $user2 = factory(User::class)->create();

        $this->createRideWithFeedback($user2, ['done' => true], ['good', 'bad', 'bad']);

        $user = factory(User::class)->create();

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 2);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 3);
        $this->assertTrue($users[0]->feedback_positivo == 2);
        $this->assertTrue($users[0]->feedback_negativo == 1);

        $this->assertTrue($users[1]->caronas == 1);
        $this->assertTrue($users[1]->caronistas == 3);
        $this->assertTrue($users[1]->feedback_positivo == 1);
        $this->assertTrue($users[1]->feedback_negativo == 2);
    }

    public function testConsiderOnlyActiveUsers()
    {
        $user2 = factory(User::class)->create(['deleted_at' => '2015-01-23']);

        $this->createRideWithFeedback($user2, ['done' => true], ['good', 'bad', 'bad']);

        $user = factory(User::class)->create();

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 3);
        $this->assertTrue($users[0]->feedback_positivo == 2);
        $this->assertTrue($users[0]->feedback_negativo == 1);
    }

    public function testIgnoreEmptyFeedbackInReputacao(){
        $user = factory(User::class)->create();

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad', null]);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 4);
        $this->assertTrue($users[0]->feedback_positivo == 2);
        $this->assertTrue($users[0]->feedback_negativo == 1);
        $this->assertTrue(round($users[0]->reputacao,1) == 0.7);
    }

    public function testIgnoreUserThatIsNotADriver(){
        factory(User::class)->create(['car_owner' => false]);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 0);
    }

    public function testNotADriverWithRideAppears(){
        $user = factory(User::class)->create(['car_owner' => false]);

        $this->createRideWithFeedback($user, ['done' => true], ['good', 'good', 'bad']);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
    }

//    public function testShowDriverWithoutARide(){
//        factory(User::class)->create(['car_owner' => true]);
//
//        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
//            Carbon::minValue(),
//            Carbon::maxValue());
//
//        $this->assertTrue(count($users) == 1);
//        $this->assertTrue($users[0]->caronas == 0);
//        $this->assertTrue($users[0]->caronistas == 0);
//        $this->assertTrue($users[0]->feedback_positivo == 0);
//        $this->assertTrue($users[0]->feedback_negativo == 0);
//        $this->assertTrue(round($users[0]->reputacao,1) == 0);
//    }

    public function testWithoutFeedbackHasNoReputation(){
        $user = factory(User::class)->create(['car_owner' => false]);

        $this->createRideWithFeedback($user, ['done' => true], [null, null, null, null]);

        $users = with(new RankingService)->getUsersOrderedByBestFeedbackInPeriod(
            Carbon::minValue(),
            Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 1);
        $this->assertTrue($users[0]->caronistas == 4);
        $this->assertTrue($users[0]->feedback_positivo == 0);
        $this->assertTrue($users[0]->feedback_negativo == 0);
        $this->assertTrue(round($users[0]->reputacao,1) == 0);
    }

}
