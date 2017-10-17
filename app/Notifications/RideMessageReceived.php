<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Caronae\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via()
    {
        return [PushChannel::class];
    }

    public function toPush()
    {
        return [
            'id'       => (string)$this->message->id,
            'title'    => $this->message->ride->title,
            'message'  => $this->message->user->name . ': ' . $this->message->body,
            'rideId'   => $this->message->ride_id,
            'senderId' => $this->message->user->id,
            'msgType'  => 'chat',
        ];
    }
}
