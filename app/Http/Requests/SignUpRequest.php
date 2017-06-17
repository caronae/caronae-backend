<?php

namespace Caronae\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'id_ufrj' => 'required|string',
            'course' => 'required|string',
            'profile' => 'required|string',
            'profile_pic_url' => 'nullable|string'
        ];
    }
}
