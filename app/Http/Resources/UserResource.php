<?php

namespace Caronae\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'profile' => $this->profile,
            'course' => $this->course,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'car_owner' => $this->car_owner,
            'car_model' => $this->car_model,
            'car_color' => $this->car_color,
            'car_plate' => $this->car_plate,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'location' => $this->location,
            'face_id' => $this->face_id,
            'profile_pic_url' => $this->profile_pic_url,
        ];
    }

    public function is(UserResource $user)
    {
        return $user->getKey() === $this->getKey();
    }
}
