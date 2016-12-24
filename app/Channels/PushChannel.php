<?php

namespace Caronae\Channels;

use Caronae\Models\User;
use Caronae\Models\Ride;
use Illuminate\Notifications\Notification;

class PushChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toPush($notifiable);

        if ($notifiable instanceof User) {
            $this->push->sendNotificationToUser($notifiable, $message);
        } else if ($notifiable instanceof Ride) {
            $this->push->sendDataToRideMembers($notifiable, $message);
        } else {
            throw new Exception("Error Processing Request", 1);
            
        }
    }
}