<?php

namespace Caronae\Services;

use Caronae\Repositories\PushNotificationInterface;
use Caronae\Models\User;

class PushNotificationService
{
    protected $push;

    public function __construct(PushNotificationInterface $push)
    {
        $this->push = $push;
    }

    public function sendNotificationToUser(User $user, $data)
    {
        return $this->push->sendNotificationToTopicId($this->topicForUser($user), $data);
    }

    private function topicForUser(User $user) 
    {
        return 'user-' . $user->id;
    }
}
