<?php

namespace Caronae\Notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RideJoinRequestAnswered extends Notification
{
    use Queueable;

    protected $ride;
    protected $requester;
    protected $accepted;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride, User $requester, bool $accepted)
    {
        $this->ride = $ride;
        $this->requester = $requester;
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
        return ['database', 'push'];
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
            'message' => $this->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(',
            'msgType' => $this->accepted ? 'accepted' : 'refused',
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
            'userID' => $this->requester->id,
            'status' => $this->accepted ? 'accepted' : 'refused'
        ];
    }
}
