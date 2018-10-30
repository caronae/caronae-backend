<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideFinished extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $user;
    public $automated;

    public function __construct(Ride $ride, bool $automated)
    {
        $this->ride = $ride;
        $this->automated = $automated;
    }

    public function via()
    {
        return ['database', PushChannel::class];
    }

    public function toPush()
    {
        return [
            'id'      => $this->id,
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
            'rideId'  => $this->ride->id,
//            'senderId' => $this->user->id,
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,
        ];
    }
}
