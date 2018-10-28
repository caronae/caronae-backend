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
        $dateString = date_string($this->ride->date);

        return [
            'id'       => $this->id,
            'title'    => $this->ride->title,
            'message'  => "{$this->requester->shortName} quer sua carona de {$dateString}",
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
