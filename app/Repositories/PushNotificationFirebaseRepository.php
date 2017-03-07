<?php

namespace Caronae\Repositories;

use Caronae\Exceptions\FirebaseException;
use Caronae\Services\PushNotificationService;

class PushNotificationFirebaseRepository implements PushNotificationInterface
{
    public function sendNotificationToDevices($tokens, $data)
    {
        $payload = $this->payloadWithData($data);

        if (is_array($tokens)) {
            $payload['registration_ids'] = $tokens;
        } else {
            $payload['to'] = $tokens;
        }

        return $this->doPost($payload);
    }

    public function sendNotificationToTopicId($topicId, $data)
    {
        $payload = $this->payloadWithData($data);
        $payload['to'] = '/topics/' . $topicId;

        return $this->doPost($payload);
    }

    private function payloadWithData($data)
    {
        $notification = [
            'body' => $data['message'],
            'icon' => 'ic_stat_name',
            'sound' => 'beep_beep.wav'
        ];

        if (!empty($data['title'])) {
            $notification['title'] = $data['title'];
        }

        return [
            'content_available' => true,
            'priority' => 'high',
            'notification' => $notification,
            'data' => $data
        ];
    }

    private function doPost($post)
    {
        //------------------------------
        // The FCM API key, generated using
        // Google APIs Console, should be
        // placed inside the .env file.
        //------------------------------

        $apiKey = env('FCM_API_KEY');

        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = [
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new FirebaseException('Error sending push notification. (' . curl_error($ch) . ')');
        }

        $info = curl_getinfo($ch);
        if (!empty($info['http_code']) && $info['http_code'] != 200) {
            curl_close($ch);
            throw new FirebaseException('Error sending push notification. (' . $info['http_code'] . ')');
        }

        curl_close($ch);
        return $result;
    }
}
