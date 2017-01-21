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

    public function sendNotificationToUser(User $user, $data)
    {
        // TODO: Deprecate after users have migrated to topic-based notifications
        if ($user->usesNotificationsWithToken()) {
            return $this->push->sendNotificationToDevices($user->gcm_token, $data);
        }

        return $this->push->sendNotificationToTopicId($this->topicForUser($user), $data);
    }

    public function sendNotificationToRideMembers(Ride $ride, $data)
    {
        $topic = $ride->id;
        return $this->push->sendNotificationToTopicId($topic, $data, true);
    }

    private function topicForUser(User $user) 
    {
        return 'user-' . $user->id;
    }
}
