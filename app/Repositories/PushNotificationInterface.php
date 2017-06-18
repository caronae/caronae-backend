<?php

namespace Caronae\Repositories;

interface PushNotificationInterface
{
    public function sendNotificationToTopicId($topicId, $data);
}
