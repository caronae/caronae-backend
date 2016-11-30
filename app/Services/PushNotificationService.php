<?php

namespace Caronae\Services;

use Caronae\Repositories\PushNotificationInterface;
use Caronae\Models\User;
use Caronae\Models\Ride;

class PushNotificationService
{
    protected $push;

    public function __construct(PushNotificationInterface $push)
    {
        $this->push = $push;
    }

    public function sendNotificationToDevices($tokens, $data)
    {
        return $this->push->sendNotificationToDevices($tokens, $data);
    }

    public function sendDataToUser(User $user, $data)
    {
        $topic = 'user-' . $user->id;
        return $this->push->sendDataToTopicId($topic, $data);
    }

    public function sendDataToRideMembers(Ride $ride, $data)
    {
        $topic = $ride->id;
        return $this->push->sendDataToTopicId($topic, $data);
    }
}
