<?php

namespace Caronae\Services;

use Caronae\Exceptions\FirebaseException;
use Caronae\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;

class PushNotificationService
{
    const FCM_API_URL = 'https://fcm.googleapis.com/fcm/';

    private $client;

    public function __construct()
    {
        $fcmApiKey = env('FCM_API_KEY');
        if (empty($fcmApiKey)) {
            throw new FirebaseException("FCM API key must be provided");
        }

        $this->client = new Client([
            'base_uri' => self::FCM_API_URL,
            'timeout' => 15.0,
            'headers' => [
                'Authorization' => 'key=' . $fcmApiKey
            ]
        ]);
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function sendNotificationToUser(User $user, $data)
    {
        $topicId = $this->topicForUser($user);
        $topicPath = '/topics/' . $topicId;
        $payload = $this->payloadWithData($data);
        $payload['to'] = $topicPath;

        Log::info('Sending push notification to ' . $topicPath);

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
        try {
            $response = $this->client->post('send', [ 'json' => $payload ]);
        } catch (RequestException $exception) {
            throw new FirebaseException('Error sending push notification. (' . $exception->getMessage() . ')');
        }

        if ($response->getStatusCode() != 200) {
            throw new FirebaseException('Error sending push notification. (HTTP ' . $response->getStatusCode() . ')');
        }
    }
}
