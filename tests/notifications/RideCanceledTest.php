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

        $this->driver = factory(User::class, 'driver')->create();
        $this->ride = factory(Ride::class)->create();
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
            'message'  => 'Um motorista cancelou uma carona ativa sua',
            'msgType'  => 'cancelled',
            'rideId'   => $this->ride->id,
            'senderId' => $this->driver->id,
        ], $payload);
    }

    /** @test */
    public function should_send_same_message_to_pending_user()
    {
        $user = factory(User::class)->create();
        $user->status = 'pending';

        $payload = $this->notification->toPush($user);

        $this->assertEquals('Um motorista cancelou uma carona que você havia solicitado', $payload['message']);
    }
}
