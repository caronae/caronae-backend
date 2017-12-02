<?php

namespace Caronae\Http\Resources;

use Illuminate\Http\Request;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRenderAsJson()
    {
        $user = factory(\Caronae\Models\User::class)->create();
        $userResource = new User($user);
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
            'profile_pic_url' => $user->profile_pic_url
        ];

        $this->assertEquals($expectedJson, $userResource->toArray(new Request()));
    }
}
