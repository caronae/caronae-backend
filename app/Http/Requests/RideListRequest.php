<?php

namespace Caronae\Http\Requests;

use Caronae\Models\Campus;
use Illuminate\Foundation\Http\FormRequest;

class RideListRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'zone' => 'string',
            'neighborhoods' => 'string',
            'place' => 'string|max:255',
            'hub' => 'string|max:255',
            'campus' => 'string|max:255',
            'going' => 'boolean',
            'date' => 'string',
            'time' => 'string',
        ];
    }

    public function filters()
    {
        $filters = [];

        if ($this->filled('going'))
            $filters['going'] = $this->input('going');

        if ($this->filled('neighborhoods'))
            $filters['neighborhoods'] = explode(', ', $this->input('neighborhoods'));

        if ($this->filled('place'))
            $filters['myplace'] = $this->input('place');

        if ($this->filled('zone'))
            $filters['myzone'] = $this->input('zone');

        if ($this->filled('campus'))
            $filters['hubs'] = Campus::findByName($this->input('campus'))->hubs()->distinct('center')->pluck('center')->toArray();

        if ($this->filled('hub'))
            $filters['hubs'] = [ $this->input('hub') ];
        else if ($this->filled('hubs'))
            $filters['hubs'] = explode(', ', $this->input('hubs'));

        return $filters;
    }
}
