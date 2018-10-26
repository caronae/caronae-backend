<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideCanceled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $driver;

    public function __construct(Ride $ride, User $driver)
    {
        $this->ride = $ride;
        $this->driver = $driver;
    }

    public function via()
    {
        return ['database', PushChannel::class];
    }

    public function toPush(User $user)
    {
        if ($user->status == 'pending') {
            $message = 'Um motorista cancelou uma carona que você havia solicitado';
        } else {
            $message = 'Um motorista cancelou uma carona ativa sua';
        }

        return [
            'id'       => $this->id,
            'message'  => $message,
            'msgType'  => 'cancelled',
            'rideId'   => $this->ride->id,
            'senderId' => $this->driver->id,
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,

        ];
    }
}
