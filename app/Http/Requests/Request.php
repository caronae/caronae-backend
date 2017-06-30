<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    public function getDateFormat(){
        return config('custom.nativeDateFormat');
    }

    public function getDate($attribute, $default = null){
        $value = $this->get($attribute);
        return $value ? Carbon::createFromFormat($this->getDateFormat(), $value) : $default;
    }
}
