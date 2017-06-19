<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Services\PushNotificationService;
use Caronae\Models\User;

class PushNotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testSendsNotificationToFirebase()
    {
        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);
        $mockHandler = new MockHandler([ new Response(200) ]);
        $handler = HandlerStack::create($mockHandler);
        $handler->push($historyMiddleware);
        $client = new Client(['handler' => $handler]);
        $push = new PushNotificationService();
        $push->setClient($client);
        $user = factory(User::class)->create();
        $data = [
            'message' => 'Example message',
            'title' => 'Example title'
        ];

        $push->sendNotificationToUser($user, $data);

        $this->assertEquals(1, count($historyContainer));

        $request = $historyContainer[0]['request'];
        $this->assertEquals('send', $request->getRequestTarget());
        $this->assertEquals('POST', $request->getMethod());

        $expectedBody = [
            'to' => '/topics/user-' . $user->id,
            'content_available' => true,
            'priority' => 'high',
            'notification' => [
                'body' => 'Example message',
                'title' => 'Example title',
                'icon' => 'ic_stat_name',
                'sound' => 'beep_beep.wav'
            ],
            'data' => $data
        ];
        $this->assertEquals($expectedBody, json_decode($request->getBody()->getContents(), true));
    }
}
