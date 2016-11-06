<?php

namespace App\Repositories;

interface PushNotificationInterface
{
    public function sendNotificationToDevices($tokens, $data);
    public function sendDataToTopicId($topicId, $data);
}
