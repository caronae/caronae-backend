<?php

namespace Caronae\Http\Resources;

use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Models\User as UserModel;
use Illuminate\Http\Request;
use Tests\TestCase;

class MessageResourceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRenderAsJson()
    {
        $ride = factory(Ride::class)->create();
        $user = factory(UserModel::class)->create();
        $message = factory(Message::class)->create(['ride_id' => $ride->id, 'user_id' => $user->id]);
        $resource = new MessageResource($message);
        $userResource = new UserResource($user);
        $expectedJson = [
            'id' => $message->id,
            'body' => $message->body,
            'date' => $message->date->toDateTimeString(),
        ];

        $response = $resource->toArray(new Request());

        $this->assertArraySubset($expectedJson, $response);
        $this->assertTrue($response['user']->is($userResource));
    }
}
