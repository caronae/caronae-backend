<?php

namespace App\Http;
use App\Exceptions\FirebaseException;

class PostGCM
{
    public static function doPost($post)
    {
        //------------------------------
        // The FCM API key, generated using
        // Google APIs Console, should be
        // placed inside the .env file.
        //------------------------------

        $apiKey = env('FCM_API_KEY');

        // $url = 'https://gcm-http.googleapis.com/gcm/send';
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

    public static function sendNotification($gcmTokens, $data) {
        $body = [
            'notification' 		=> ['body' => $data['message']],
            'content_available' => true,
            'data' 				=> $data
        ];

        if (is_array($gcmTokens)) {
            $body['registration_ids'] = $gcmTokens;
        } else {
            $body['to'] = $gcmTokens;
        }

        return self::doPost($body);
    }

    public static function sendDataToTopic($topic, $data) {
        $body = [
            'to' 		        => $topic,
            'priority'          => 'high',
            'content_available' => true,
            'data'              => $data
        ];

        return self::doPost($body);
    }
}
