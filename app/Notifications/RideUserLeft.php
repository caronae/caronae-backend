<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideUserLeft extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $user;

    public function __construct(Ride $ride, User $user)
    {
        $this->ride = $ride;
        $this->user = $user;
    }

    public function via()
    {
        return ['database', PushChannel::class];
    }

    public function toPush()
    {
        return [
            'id'       => $this->id,
            'message'  => 'Um caronista desistiu de sua carona',
            'msgType'  => 'quitter',
            'rideId'   => $this->ride->id,
            'senderId' => $this->user->id,
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,
            'userID' => $this->user->id,
        ];
    }
}
