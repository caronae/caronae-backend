<?php

namespace App\Http\Requests;

class CarbonTaxRequest extends Request
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
            'start' => 'date_format:'.$this->getDateFormat(),
            'end' => ['date_format:'.$this->getDateFormat(), 'after_or_equals:start']
        ];
    }

    public function messages(){
        return [
            'start.required' => 'É obrigatória a indicação de um valor para o campo "De".',
            'end.required' => 'É obrigatória a indicação de um valor para o campo "Até".',
            'end.after_or_equals' => 'O fim do período deve ser depois do começo do período.'
        ];
    }
}
