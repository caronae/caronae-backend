<?php


namespace App\Validators;


use Carbon\Carbon;
use Illuminate\Validation\Validator;
use ReflectionMethod;

class AfterOrEqualsValidator
{
    public function getDateFormat(Validator $validator, $attribute){
        /**
         * O Validator não expoe o formato de data que deve ser usado.
         * Logo, uso reflection para obter isso
         */
        $method = new ReflectionMethod(Validator::class, 'getDateFormat');
        $method->setAccessible(true);
        return $method->invoke($validator, $attribute);
    }

    public function validate($attribute, $value, $parameters, Validator $validator) {
        $data = $validator->getData();

        $otherField = $data[$parameters[0]];

        $format = $this->getDateFormat($validator, $attribute);
        if($format){
            return Carbon::createFromFormat($format, $value)->gte(Carbon::createFromFormat($format, $otherField));
        } else {
            return strtotime($value) >= strtotime($otherField);
        }
    }
}