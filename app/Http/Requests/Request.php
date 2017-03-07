<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    public function getDateFormat(){
        return config('custom.nativeDateFormat');
    }

    /**
     * @param $attribute
     * @param null $default
     * @return \Carbon\Carbon
     *
     * Usado nos controllers para facilitar a obtenção de uma data
     * Carbon a partir de uma data enviada via request.
     */
    public function getDate($attribute, $default = null){
        $value = $this->get($attribute);
        return $value ? Carbon::createFromFormat($this->getDateFormat(), $value) : $default;
    }
}
