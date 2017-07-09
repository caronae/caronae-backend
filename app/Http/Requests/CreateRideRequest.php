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
            $this->parseDate();

            if ($this->input('date')->isPast()) {
                $validator->errors()->add('mydate', 'You cannot create a ride in the past.');
            }
        });
    }

    protected function parseDate()
    {
        $mydate = $this->input('mydate');
        $mytime = substr($this->input('mytime'), 0, 5);
        $dateTime = $mydate . ' ' . $mytime;

        try {
            $date = Carbon::createFromFormat('d/m/Y H:i', $dateTime);
        } catch (\InvalidArgumentException $error) {
            $date = Carbon::createFromFormat('Y-m-d H:i', $dateTime);
        }

        $this->merge([
            'date' => $date
        ]);
    }

    public function isRoutine()
    {
        $repeatsUntil = $this->input('repeats_until');
        return (!empty($repeatsUntil) && is_string($repeatsUntil));
    }

    public function getRoutineEndDate()
    {
        $repeatsUntil = $this->input('repeats_until');
        try {
            $date = Carbon::createFromFormat('d/m/Y', $repeatsUntil);
        } catch(\InvalidArgumentException $error) {
            $date = Carbon::createFromFormat('Y-m-d', $repeatsUntil);
        }
        return $date->setTime(23,59,59);
    }

    public function response(array $errors)
    {
        return new JsonResponse($errors, 422);
    }
}
