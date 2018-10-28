<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideJoinRequestAnswered;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class RideJoinRequestAnsweredTest extends TestCase
{
    use DatabaseTransactions;

	private $ride;
    private $driver;

    public function setUp()
    {
        parent::setUp();

        $this->driver = factory(User::class, 'driver')->create(['name' => 'Fulana Santos Silva']);
        $this->ride = factory(Ride::class)->create(['date' => '2018-11-02 20:00:00']);
        $this->ride->users()->attach($this->driver, ['status' => 'driver']);
    }

    /** @test */
    public function should_contain_all_fields_in_push_when_accepted()
    {
    	$notification = new RideJoinRequestAnswered($this->ride, true);
        $notification->id = uniqid();

        $this->assertSame([
            'id'       => $notification->id,
            'title'    => $this->ride->title,
            'message'  => 'Fulana Silva aceitou seu pedido de carona de sexta-feira (02/11) às 20:00 =)',
            'msgType'  => 'accepted',
            'rideId'   => $this->ride->id,
            'senderId' => $this->driver->id,
        ], $notification->toPush(Mockery::mock(User::class)));
    }

    /** @test */
    public function should_change_message_when_rejected()
    {
    	$notification = new RideJoinRequestAnswered($this->ride, false);
        $notification->id = uniqid();

        $toPush = $notification->toPush(factory(User::class));
        $this->assertEquals('Fulana Silva recusou seu pedido de carona de sexta-feira (02/11) às 20:00 =(', $toPush['message']);
    }
}
