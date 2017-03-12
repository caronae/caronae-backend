<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class RideJoinRequestAnswered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $accepted;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride, bool $accepted)
    {
        $this->ride = $ride;
        $this->accepted = $accepted;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', PushChannel::class];
    }

    /**
     * Get the mobile push representation of the notification.
     *
     * @param  User  $notifiable
     * @return array
     */
    public function toPush($notifiable)
    {
        return [
            'id'       => $this->id,
            'message'  => $this->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(',
            'msgType'  => $this->accepted ? 'accepted' : 'refused',
            'rideId'   => $this->ride->id,
            'senderId' => $this->ride->driver()->id
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  User  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'rideID' => $this->ride->id,
            'status' => $this->accepted ? 'accepted' : 'refused'
        ];
    }
}
