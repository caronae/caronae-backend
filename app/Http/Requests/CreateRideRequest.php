<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class CreateRideRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'myzone' => 'required|string',
            'neighborhood' => 'required|string',
            'place' => 'string|max:255',
            'route' => 'string|max:255',
            'slots' => 'required|numeric|max:10',
            'hub' => 'required|string|max:255',
            'description' => 'string|max:255',
            'going' => 'required|boolean',
            'mydate' => 'required|string',
            'mytime' => 'required|string'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mydate = $this->input('mydate');
            $mytime = substr($this->input('mytime'), 0, 5);
            $dateTime = $mydate . ' ' . $mytime;

            try {
                $date = Carbon::createFromFormat('d/m/Y H:i', $dateTime);
            } catch(\InvalidArgumentException $error) {
                $date = Carbon::createFromFormat('Y-m-d H:i', $dateTime);
            }

            if ($date->isPast()) {
                $validator->errors()->add('mydate', 'You cannot create a ride in the past.');
            }

            $this->merge([
                'date' => $date
            ]);
        });
    }

    public function response(array $errors)
    {
        return new JsonResponse($errors, 422);
    }
}
