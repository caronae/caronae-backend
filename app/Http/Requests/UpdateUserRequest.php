<?php

namespace Caronae\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone_number' => 'numeric|max:999999999999',
            'email' => 'email',
            'location' => 'string',
            'car_owner' => 'boolean',
            'car_model' => 'required_if:car_owner,true,1|string|max:25',
            'car_color' => 'required_if:car_owner,true,1|string|max:25',
            'car_plate' => 'required_if:car_owner,true,1|regex:/[a-zA-Z0-9\-]{7,8}$/',
            'profile_pic_url' => 'url',
            'facebook_id' => 'string',
        ];
    }

    public function profile()
    {
        $attributes = $this->only([
            'phone_number',
            'location',
            'car_owner',
            'car_model',
            'car_plate',
            'car_color',
            'profile_pic_url',
        ]);

        if ($this->has('facebook_id')) {
            $attributes['face_id'] = $this->input('facebook_id');
        }

        if ($this->has('email')) {
            $attributes['email'] = strtolower($this->input('email'));
        }

        return $attributes;
    }
}
