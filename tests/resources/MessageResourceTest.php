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
    public function should_render_as_json()
    {
        $ride = factory(Ride::class)->create();
        $user = factory(UserModel::class)->create()->fresh();
        $message = factory(Message::class)->create(['ride_id' => $ride->id, 'user_id' => $user->id]);
        $resource = new MessageResource($message);
        $userResource = new UserResource($user);
        $expectedJson = [
            'id' => $message->id,
            'body' => $message->body,
            'date' => $message->date->toDateTimeString(),
            'user' => $userResource,
        ];

        $this->assertEquals($expectedJson, $resource->resolve());
    }
}
