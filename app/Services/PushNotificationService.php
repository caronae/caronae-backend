<?php

namespace Caronae\Services;

use Caronae\Models\User;
use GuzzleHttp\Client;
use Recurr\Exception;

class PushNotificationService
{
    const FCM_API_URL = 'https://fcm.googleapis.com/fcm';

    private $client;

    public function __construct(Client $client = null)
    {
        if ($client == null) {
            $client = new Client([
                'base_uri' => FIREBASE_API_URL,
                'timeout' => 15.0,
            ]);
        }

        $this->client = $client;
    }

    public function sendNotificationToUser(User $user, $data)
    {
        $topicId = $this->topicForUser($user);
        $payload = $this->payloadWithData($data);
        $payload['to'] = '/topics/' . $topicId;

        $this->sendFCMRequest($payload);
    }

    private function topicForUser(User $user) 
    {
        return 'user-' . $user->id;
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

    private function sendFCMRequest($payload)
    {
        $headers = [
            'Authorization: key=' . env('FCM_API_KEY')
        ];

        try {
            $response = $this->client->post('/send', ['json' => $payload, 'headers' => $headers]);
        } catch (Exception $exception) {
            throw new FirebaseException('Error sending push notification. (' . $exception->getMessage() . ')');
        }

        if ($response->getStatusCode() != 200) {
            throw new FirebaseException('Error sending push notification. (HTTP ' . $response->getStatusCode() . ')');
        }
    }
}
