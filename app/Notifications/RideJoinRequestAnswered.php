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
        return [
            'id'       => $this->id,
            'message'  => $this->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(',
            'msgType'  => $this->accepted ? 'accepted' : 'refused',
            'rideId'   => $this->ride->id,
            'senderId' => $this->ride->driver()->id
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,
            'status' => $this->accepted ? 'accepted' : 'refused'
        ];
    }
}
