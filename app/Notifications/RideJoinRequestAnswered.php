<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideJoinRequestAnswered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $accepted;

    public function __construct(Ride $ride, bool $accepted)
    {
        $this->ride = $ride;
        $this->accepted = $accepted;
    }

    public function via()
    {
        return ['database', PushChannel::class];
    }

    public function toPush()
    {
        $dateString = date_string($this->ride->date);
        $driver = $this->ride->driver();

        if ($this->accepted) {
            $action = 'aceitou';
            $emoji = '=)';
        } else {
            $action = 'recusou';
            $emoji = '=(';
        }

        return [
            'id'       => $this->id,
            'title'    => $this->ride->title,
            'message'  => "{$driver->shortName} {$action} seu pedido de carona de {$dateString} {$emoji}",
            'msgType'  => $this->accepted ? 'accepted' : 'refused',
            'rideId'   => $this->ride->id,
            'senderId' => $driver->id,
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,
            'status' => $this->accepted ? 'accepted' : 'refused',
        ];
    }
}
