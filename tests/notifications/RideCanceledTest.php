<?php

namespace Tests\notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RideCanceledTest extends TestCase
{
    use DatabaseTransactions;

    protected $notification;

    private $ride;
    private $driver;

    public function setUp()
    {
        parent::setUp();

        $this->driver = factory(User::class, 'driver')->create(['name' => 'Fulana Santos Silva']);
        $this->ride = factory(Ride::class)->create(['date' => '2018-11-02 20:00:00']);
        $this->ride->users()->attach($this->driver, ['status' => 'driver']);

        $this->notification = new RideCanceled($this->ride, $this->driver);
        $this->notification->id = uniqid();
    }

    /** @test */
    public function should_contain_all_fields_in_push()
    {
        $user = factory(User::class)->create();
        $user->status = 'accepted';

        $payload = $this->notification->toPush($user);

        $this->assertSame([
            'id'       => $this->notification->id,
            'title'    => $this->ride->title,
            'message'  => 'Fulana Silva teve que cancelar sua carona de sexta-feira (02/11) às 20:00',
            'msgType'  => 'cancelled',
            'rideId'   => $this->ride->id,
            'senderId' => $this->driver->id,
        ], $payload);
    }
}
