<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideJoinRequested;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RideJoinRequestedTest extends TestCase
{
    use DatabaseTransactions;

    protected $notification;
    private $user;
    private $ride;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class, 'driver')->create(['name' => 'Fulana Santos Silva']);
        $this->ride = factory(Ride::class)->create(['date' => '2018-11-02 20:00:00']);

        $this->notification = new RideJoinRequested($this->ride, $this->user);
        $this->notification->id = uniqid();
    }

    /** @test */
    public function should_contain_all_fields_in_push()
    {
        $driver = factory(User::class);
        $this->assertSame([
            'id'       => $this->notification->id,
            'title'    => $this->ride->title,
            'message'  => 'Fulana Silva quer sua carona de sexta-feira (02/11) às 20:00',
            'msgType'  => 'joinRequest',
            'rideId'   => $this->ride->id,
            'senderId' => $this->user->id,
        ], $this->notification->toPush($driver));
    }
}
