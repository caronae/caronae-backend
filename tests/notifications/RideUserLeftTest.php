<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideUserLeft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class RideUserLeftTest extends TestCase
{
    use DatabaseTransactions;

	private $notification;
    private $user;
    private $ride;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class, 'driver')->create(['name' => 'Fulana Santos Silva']);
        $this->ride = factory(Ride::class)->create(['date' => '2018-11-02 20:00:00']);

        $this->notification = new RideUserLeft($this->ride, $this->user);
        $this->notification->id = uniqid();
	}

    /** @test */
    public function should_contain_all_fields_in_push()
    {
        $driver = factory(User::class);
        $this->assertSame([
            'id'       => $this->notification->id,
            'title'    => $this->ride->title,
            'message'  => 'Fulana Silva desistiu da sua carona de sexta-feira (02/11) às 20:00',
            'msgType'  => 'quitter',
            'rideId'   => $this->ride->id,
            'senderId' => $this->user->id,
        ], $this->notification->toPush($driver));
    }
}
