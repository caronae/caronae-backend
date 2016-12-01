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

    public function sendNotificationToUser(User $user, $data)
    {
        // TODO: Deprecate
        if (!empty($user->gcm_token)) {
            $this->push->sendNotificationToDevices($user->gcm_token, $data);
        }

        return $this->push->sendNotificationToTopicId($this->topicForUser($user), $data);
    }

    public function sendDataToUser(User $user, $data)
    {
        return $this->push->sendDataToTopicId($this->topicForUser($user), $data);
    }

    public function sendDataToRideMembers(Ride $ride, $data)
    {
        $topic = $ride->id;
        return $this->push->sendDataToTopicId($topic, $data);
    }

    private function topicForUser(User $user) 
    {
        return 'user-' . $user->id;
    }
}
