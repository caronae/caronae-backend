<?php

namespace Caronae\Http\Resources;

use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function should_render_as_json()
    {
        $user = factory(User::class)->create();
        $userResource = new UserResource($user);
        $expectedJson = [
            'id' => $user->id,
            'name' => $user->name,
            'profile' => $user->profile,
            'course' => $user->course,
            'phone_number' => $user->phone_number,
            'email' => $user->email,
            'car_owner' => $user->car_owner,
            'car_model' => $user->car_model,
            'car_color' => $user->car_color,
            'car_plate' => $user->car_plate,
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'location' => $user->location,
            'face_id' => $user->face_id,
            'profile_pic_url' => $user->profile_pic_url,
        ];

        $this->assertEquals($expectedJson, $userResource->toArray(new Request()));
    }
}
