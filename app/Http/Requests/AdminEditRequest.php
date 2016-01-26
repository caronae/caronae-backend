<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AdminEditRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'min:4|confirmed'
        ];
    }

    public function messages(){
        return [
            'password.min' => "O campo senha deverá conter no mínimo 4 caracteres.",
            'password.confirmed' => "A confirmação para o campo senha não coincide."
        ];
    }
}
