<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
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
            'going' => 'required|boolean'
        ];
    }

    public function searchDate()
    {
        return Carbon::createFromFormat('d/m/Y H:i:s', $this->input('date') . ' ' . $this->input('time'));
    }

    public function searchRange()
    {
        $date = $this->searchDate();
        $dateMin = $date->copy()->setTime(0,0,0)->max(Carbon::now());
        $dateMax = $date->copy()->setTime(23,59,59);
        return [$dateMin, $dateMax];
    }
}
