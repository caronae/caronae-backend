<?php

namespace Caronae\Repositories;

interface PushNotificationInterface
{
    public function sendNotificationToDevices($tokens, $data);
    public function sendNotificationToTopicId($topicId, $data);
    public function sendDataToTopicId($topicId, $data);
}
