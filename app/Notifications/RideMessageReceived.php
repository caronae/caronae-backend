<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class RideMessageReceived extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [PushChannel::class];
    }

    /**
     * Get the mobile push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toPush($notifiable)
    {
        $alertMessage = $this->message->user->name . ': ' . $this->message->body;
        return [
            'message' => $alertMessage,
            'rideId' => $this->message->ride_id,
            'senderId' => $this->message->user->id,
            'msgType' => 'chat'
        ];
    }
}
