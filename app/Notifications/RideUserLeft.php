<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class RideUserLeft extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ride;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride, User $user)
    {
        $this->ride = $ride;
        $this->user = $user;
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
        // TODO: Include the rider's name in the notification
        return [
            'message' => 'Um caronista desistiu de sua carona',
            'msgType' => 'quitter',
            'rideId'  => $this->ride->id
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
            'userID' => $this->user->id
        ];
    }
}
