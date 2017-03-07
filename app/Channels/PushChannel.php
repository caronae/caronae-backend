<?php

namespace Caronae\Channels;

use Caronae\Models\User;
use Caronae\Models\Ride;
use Caronae\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

class PushChannel
{
    protected $push;
    
    public function __construct(PushNotificationService $push)
    {
        $this->push = $push;
    }
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
            $this->push->sendNotificationToRideMembers($notifiable, $message);
        } else {
            throw new Exception("Invalid notifiable instance to notify via mobile push.");
        }
    }
}