<?php


namespace App\Validators;


use Carbon\Carbon;
use Illuminate\Validation\Validator;

class AfterOrEqualsValidator
{
    public function validate($attribute, $value, $parameters, Validator $validator) {
        $data = $validator->getData();

        $otherField = $data[$parameters[0]];

        return Carbon::createFromFormat('d/m/Y', $value)->gte(Carbon::createFromFormat('d/m/Y', $otherField));
    }
}