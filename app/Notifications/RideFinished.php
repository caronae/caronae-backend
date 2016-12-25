<?php

namespace Caronae\Notifications;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RideFinished extends Notification
{
    use Queueable;

    protected $ride;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride)
    {
        $this->ride = $ride;
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
        // TODO: Include the driver's name in the notification
        return [
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
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
            'rideID' => $this->ride->id
        ];
    }
}
