<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Ride;
use App\RideUser;

class RideTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $headers;

    /**
    * @before
    */
    public function cleanDatabase()
    {
        $this->beginDatabaseTransaction();

        DB::table('ride_user')->delete();
        DB::table('users')->delete();
        DB::table('rides')->delete();
    }

    /**
    * @before
    */
    public function createFakeUserHeaders()
    {
        $user = factory(User::class)->create();
        $this->user = User::find($user->id);
        $this->headers = ['token' => $this->user->token];
    }

    public function testGetAll()
    {
        $user = $this->user;
        $rideIds = [];
        $rides = factory(Ride::class, 'next', 3)->create(['done' => false])->each(function($ride) use ($user, &$rideIds) {
            $ride->users()->attach($user, ['status' => 'driver']);
            $rideIds[] = $ride->id;
        });

        $rides = Ride::findMany($rideIds);
        foreach ($rides as $ride) {
            $ride->driver = $user->toArray();
            unset($ride->done);
        }

        $response = $this->json('GET', 'ride/all', [], $this->headers);
        $response->assertResponseOk();

        $response->seeJsonEquals($rides->toArray());
    }

    public function testSearch()
    {

    }

    public function testCreateWithoutRoutine()
    {
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '20/10/2016',
            'mytime' => '10:00',
            'week_days' => NULL,
            'repeats_until' => NULL,
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertResponseOk();

        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-20',
            'mytime' => '10:00',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => false
        ]);

        // $response->seeJsonStructure([
        //     '*' => ['id']
        // ]);
    }

    public function testCreateWithRoutine()
    {
        $request = [
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '24/10/2016',
            'mytime' => '16:40',
            'week_days' => '2,4', // tuesday, thursday
            'repeats_until' => '01/11/2016',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ];

        $response = $this->json('POST', 'ride', $request, $this->headers);
        $response->assertResponseOk();

        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-25',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);
        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-10-27',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);
        $response->seeJsonContains([
            'myzone' => 'Norte',
            'neighborhood' => 'Jardim Guanabara',
            'place' => 'Praia da bica',
            'route' => 'Linha Vermelha',
            'mydate' => '2016-11-01',
            'mytime' => '16:40',
            'slots' => '4',
            'hub' => 'A',
            'description' => 'Lorem ipsum dolor',
            'going' => true
        ]);

        // $response->seeJsonStructure([
        //     '*' => ['id', 'routine_id']
        // ]);
    }

    public function testDelete()
    {

    }

    public function testDeleteAllFromRoutine()
    {

    }

    public function testJoin()
    {

    }

    public function testGetRequesters()
    {

    }

    public function testAnswerJoinRequest()
    {

    }

    public function testLeave()
    {

    }

    public function testFinish()
    {

    }

    public function testSaveFeedback()
    {

    }

    public function testGetActiveRides()
    {

    }

    public function testGetHistory()
    {

    }

    public function testGetHistoryCount()
    {

    }
}
