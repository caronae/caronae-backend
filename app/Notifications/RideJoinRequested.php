<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RideJoinRequested extends Notification
{
    use Queueable;

    protected $ride;
    protected $requester;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride, User $requester)
    {
        $this->ride = $ride;
        $this->requester = $requester;
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
        // TODO: Include the requester's name in the notification
        return [
            'message' => 'Sua carona recebeu uma solicitação',
            'msgType' => 'joinRequest',
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
            'userID' => $this->requester->id
        ];
    }
}
