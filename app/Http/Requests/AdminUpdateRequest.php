<?php

namespace Caronae\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class AdminUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
        ];
    }

    protected function getValidatorInstance()
    {
        if ($this->isEmptyString('password')) {
            $this->replace($this->except('password'));
        } else {
            $this->merge([
                'password' => Hash::make($this->input('password')),
            ]);
        }

        return parent::getValidatorInstance();
    }
}
