<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideJoinRequested extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $requester;

    public function __construct(Ride $ride, User $requester)
    {
        $this->ride = $ride;
        $this->requester = $requester;
    }

    public function via()
    {
        return ['database', PushChannel::class];
    }

    public function toPush()
    {
        return [
            'id'       => $this->id,
            'message'  => 'Sua carona recebeu uma solicitação',
            'msgType'  => 'joinRequest',
            'rideId'   => $this->ride->id,
            'senderId' => $this->requester->id,
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,
            'userID' => $this->requester->id,
        ];
    }
}
