<?php

namespace Caronae\Http\Resources;

use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Models\User as UserModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MessageResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_render_as_json_and_decrypt_message_body()
    {
        $ride = factory(Ride::class)->create();
        $user = factory(UserModel::class)->create()->fresh();
        $message = Message::create([
            'ride_id' => $ride->id,
            'user_id' => $user->id,
            'body' => 'Hello World!',
        ]);
        $resource = new MessageResource($message);
        $userResource = new UserResource($user);
        $expectedJson = [
            'id' => $message->id,
            'body' => 'Hello World!',
            'date' => $message->date->toDateTimeString(),
            'user' => $userResource,
        ];

        $this->assertEquals($expectedJson, $resource->resolve());
    }
}
