<?php

use App\RankingService;
use App\Ride;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class RankinggetDriversOrderedByRidesInPeriodTest extends TestCase
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

    public function createRides($user, $rideAttrs){
        factory(User::class, count($rideAttrs))->create()->each(function($u, $i) use($user, $rideAttrs) {
            $ride = factory(Ride::class)->create($rideAttrs[$i]);
            $u->rides()->save($ride, ['status' => 'driver']);
            $user->rides()->save($ride, ['status' => 'accepted']);
        });
    }

    public function testCaronasHaveCorrectValue()
    {
        $user = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->carbono_economizado == 9379.6);
    }

    public function testOnlyConsiderDoneRides()
    {
        $user = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => false, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 3);
        $this->assertTrue($users[0]->carbono_economizado == 9379.6);

    }

    public function testOnlyConsiderInsidePeriod()
    {
        $user = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user, [
            ['done' => true, 'mydate' => '2015-01-08', 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'mydate' => '2015-01-10', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'mydate' => '2015-01-10', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
            ['done' => true, 'mydate' => '2015-01-12', 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 9), Carbon::createFromDate(2015, 1, 11));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
        $this->assertTrue($users[0]->carbono_economizado == 3576.3);
    }

    public function testOnlyConsiderInsidePeriodInclusive()
    {
        $user = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user, [
            ['done' => true, 'mydate' => '2015-01-08', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'mydate' => '2015-01-10', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
            ['done' => true, 'mydate' => '2015-01-12', 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 8), Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
        $this->assertTrue($users[0]->carbono_economizado == 3576.3);
    }

    public function testCanSelectOneDayPeriod()
    {
        $user = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user, [
            ['done' => true, 'mydate' => '2015-01-08', 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'mydate' => '2015-01-10', 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'mydate' => '2015-01-10', 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::createFromDate(2015, 1, 10), Carbon::createFromDate(2015, 1, 10));

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 2);
        $this->assertTrue($users[0]->carbono_economizado == 3576.3);
    }

    public function testOrderCorrectly()
    {
        $user2 = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user2, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $user = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 2);
        $this->assertTrue($users[0]->caronas == 4);
        $this->assertTrue($users[0]->carbono_economizado == 11266);

        $this->assertTrue($users[1]->caronas == 3);
        $this->assertTrue($users[1]->carbono_economizado == 9379.6);

    }

    public function testConsiderOnlyActiveUsers()
    {
        $user = factory(User::class)->create(['car_owner' => true, 'deleted_at' => '2015-01-23']);

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $user2 = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user2, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 4);
        $this->assertTrue($users[0]->carbono_economizado == 11266);
    }

    public function testConsiderOnlyCarOwners()
    {
        $user = factory(User::class)->create(['car_owner' => false]);

        $this->createRides($user, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé']
        ]);

        $user2 = factory(User::class)->create(['car_owner' => true]);

        $this->createRides($user2, [
            ['done' => true, 'myzone' => 'Centro', 'neighborhood' => 'São Cristóvão'],
            ['done' => true, 'myzone' => 'Zona Norte', 'neighborhood' => 'Tijuca'],
            ['done' => true, 'myzone' => 'Baixada', 'neighborhood' => 'Magé'],
            ['done' => true, 'myzone' => 'Zona Sul', 'neighborhood' => 'Catete'],
        ]);

        $users = with(new RankingService)->getDriversOrderedByRidesInPeriod(Carbon::minValue(), Carbon::maxValue());

        $this->assertTrue(count($users) == 1);
        $this->assertTrue($users[0]->caronas == 4);
        $this->assertTrue($users[0]->carbono_economizado == 11266);
    }

}
