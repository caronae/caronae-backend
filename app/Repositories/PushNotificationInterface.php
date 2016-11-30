<?php

namespace Caronae\Repositories;

interface PushNotificationInterface
{
    public function sendNotificationToDevices($tokens, $data);
    public function sendDataToTopicId($topicId, $data);
}
