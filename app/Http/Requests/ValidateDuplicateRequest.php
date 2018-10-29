<?php

namespace Caronae\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateDuplicateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'time' => 'required|date_format:H:i:s',
            'going' => 'required|boolean',
        ];
    }
}
