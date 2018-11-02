<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideFinished;
use Mockery;
use Tests\TestCase;

class RideFinishedTest extends TestCase
{
    private $notification;
    private $driver;
    private $ride;

    public function setUp()
    {
        parent::setUp();

        $this->driver = factory(User::class, 'driver')->create(['name' => 'Fulana Santos Silva']);
        $this->ride = factory(Ride::class)->create(['date' => '2018-11-02 20:00:00']);
        $this->ride->users()->attach($this->driver, ['status' => 'driver']);

        $this->notification = new RideFinished($this->ride);
        $this->notification->id = uniqid();
    }

    /** @test */
    public function should_contain_all_fields_in_push()
    {
        $this->assertSame([
            'id'       => $this->notification->id,
            'title'    => $this->ride->title,
            'message'  => 'Deu tudo certo com a sua carona? Use o Falaê para reportar qualquer problema ou nos mandar seu feedback. Obrigado por usar o Caronaê! ;)',
            'msgType'  => 'finished',
            'rideId'   => $this->ride->id,
            'senderId' => $this->driver->id,
        ], $this->notification->toPush(Mockery::mock(User::class)));
    }
}
