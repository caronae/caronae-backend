<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideFinished extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $user;
    public $automated;

    public function __construct(Ride $ride, bool $automated = false)
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
            'id'       => $this->id,
            'title'    => $this->ride->title,
            'message'  => 'Deu tudo certo com a sua carona? Use o Falaê para reportar qualquer problema ou nos mandar seu feedback. Obrigado por usar o Caronaê! ;)',
            'msgType'  => 'finished',
            'rideId'   => $this->ride->id,
            'senderId' => $this->ride->driver()->id,
        ];
    }

    public function toArray()
    {
        return [
            'rideID' => $this->ride->id,
        ];
    }
}
