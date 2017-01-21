<?php

use Caronae\Services\RankingService;
use Caronae\Models\Ride;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Caronae\Models\User;

class RankingGetDriversOrderedByRidesInPeriodTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        DB::table('neighborhoods')->delete();
        $this->seed('BootstrapSeeder');
    }

    /**
     * @before
     */
    public function cleanDatabase()
    {
        DB::table('ride_user')->delete();
        DB::table('users')->delete();
        DB::table('rides')->delete();
    }

    private function getDriver($firstTimeAsDriver = null){
        $firstTimeAsDriver = $firstTimeAsDriver ?: Carbon::minValue()->format('Y-m-d');
        $user = factory(User::class)->create();
        $ride = factory(Ride::class)->create(['done' => true, 'date' => $firstTimeAsDriver]);
        $user->rides()->save($ride, ['status' => 'driver']);

        return $user;
    }

    public function createRides($user, $rideAttrs){
        factory(User::class, count($rideAttrs))->create()->each(function($u, $i) use($user, $rideAttrs) {
            $ride = factory(Ride::class)->create($rideAttrs[$i]);
            $u->rides()->save($ride, ['status' => 'driver']);
            $user->rides()->save($ride, ['status' => 'accepted']);
        });
    }

    public function testRidesHaveCorrectValue()
    {
        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(1, count($users));
        $this->assertEquals(3, $users[0]->caronas);
        $this->assertEquals(9379.6, $users[0]->carbono_economizado);
    }

    public function testOnlyConsiderDoneRides()
    {
        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => false, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(1, count($users));
        $this->assertEquals(3, $users[0]->caronas);
        $this->assertEquals(9379.6, $users[0]->carbono_economizado);

    }

    public function testOnlyConsiderInsidePeriod()
    {
        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'date' => '2015-01-08', 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'date' => '2015-01-10', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'date' => '2015-01-10', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
            ['done' => true, 'date' => '2015-01-12', 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 9), Carbon::createFromDate(2015, 1, 11));

        $this->assertEquals(1, count($users));
        $this->assertEquals(2, $users[0]->caronas);
        $this->assertEquals(3576.3, $users[0]->carbono_economizado);
    }

    public function testOnlyConsiderInsidePeriodInclusive()
    {
        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'date' => '2015-01-08', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'date' => '2015-01-10', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
            ['done' => true, 'date' => '2015-01-12', 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 8), Carbon::createFromDate(2015, 1, 10));

        $this->assertEquals(1, count($users));
        $this->assertEquals(2, $users[0]->caronas);
        $this->assertEquals(3576.3, $users[0]->carbono_economizado);
    }

    public function testCanSelectOneDayPeriod()
    {
        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'date' => '2015-01-08', 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'date' => '2015-01-10', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'date' => '2015-01-10', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 10), Carbon::createFromDate(2015, 1, 10));

        $this->assertEquals(1, count($users));
        $this->assertEquals(2, $users[0]->caronas);
        $this->assertEquals(3576.3, $users[0]->carbono_economizado);
    }

    public function testOrderCorrectly()
    {
        $user2 = $this->getDriver();

        $this->createRides($user2, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(2, count($users));
        $this->assertEquals(4, $users[0]->caronas);
        $this->assertEquals(11266, $users[0]->carbono_economizado);

        $this->assertEquals(3, $users[1]->caronas);
        $this->assertEquals(9379.6, $users[1]->carbono_economizado);

    }

    public function testConsiderOnlyActiveUsers()
    {
        $user = $this->getDriver();
        $user->deleted_at = '2015-01-23';
        $user->save();

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $user2 = $this->getDriver();

        $this->createRides($user2, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(1, count($users));
        $this->assertEquals(4, $users[0]->caronas);
        $this->assertEquals(11266, $users[0]->carbono_economizado);
    }

    public function testConsiderOnlyDrivers()
    {
        $user = factory(User::class)->create();

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $user2 = $this->getDriver();

        $this->createRides($user2, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(1, count($users));
        $this->assertEquals(4, $users[0]->caronas);
        $this->assertEquals(11266, $users[0]->carbono_economizado);
    }

    public function testConsiderRidesToUndefinedPlaces()
    {
        $user = $this->getDriver();

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
            ['done' => true, 'myzone' => 'Outros', 'neighborhood' => 'Petrópolis'], // Outros/Petrópolis is not on database
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(1, count($users));
        $this->assertEquals(5, $users[0]->caronas);
        $this->assertEquals(11266, $users[0]->carbono_economizado);
    }

    // TODO: Fix test
    // public function testConsiderOnlyDriversInPeriod()
    // {
    //     $user = $this->getDriver(Carbon::maxValue()->format('Y-m-d'));

    //     $this->createRides($user, [
    //         ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
    //         ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
    //         ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
    //     ]);

    //     $user2 = $this->getDriver();

    //     $this->createRides($user2, [
    //         ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
    //         ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
    //         ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
    //         ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
    //     ]);

    //     $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::createFromFormat('Y-m-d', '2015-01-23'));

    //     $this->assertEquals(1, count($users));
    //     $this->assertEquals(4, $users[0]->caronas);
    //     $this->assertEquals(11266, $users[0]->carbono_economizado);
    // }

    public function testConsiderOnlyRidesAfterBecomingADriver()
    {
        $user = $this->getDriver('2015-06-21');

        $this->createRides($user, [
            ['done' => true, 'date' => '2015-06-20', 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'date' => '2015-06-20', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'date' => '2015-06-21', 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'date' => '2015-06-21', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'date' => '2015-06-21', 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'date' => '2015-06-21', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertEquals(1, count($users));
        $this->assertEquals(4, $users[0]->caronas);
        $this->assertEquals(11266, $users[0]->carbono_economizado);
    }

}
