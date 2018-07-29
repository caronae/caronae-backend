<?php

namespace Caronae\Notifications;

use Caronae\Channels\PushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct($message = 'Hello World!')
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
            'id'      => $this->id,
            'message' => $this->message,
        ];
    }
}
