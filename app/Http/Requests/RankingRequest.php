<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RankingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start' => ['required', 'date_format:' . $this->getDateFormat()],
            'end' => ['required', 'date_format:' . $this->getDateFormat(), 'after_or_equals:start']
        ];
    }

    public function messages()
    {
        return [
            'start.required' => 'É obrigatória a indicação de um valor para o campo "De".',
            'end.required' => 'É obrigatória a indicação de um valor para o campo "Até".',
            'end.after_or_equals' => 'O fim do período deve ser depois do começo do período.'
        ];
    }

    public function getDate($attribute, $default = null)
    {
        $value = $this->get($attribute);
        return $value ? Carbon::createFromFormat($this->getDateFormat(), $value) : $default;
    }

    private function getDateFormat()
    {
        return config('custom.nativeDateFormat');
    }
}
