<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
use Caronae\Models\Campus;
use Caronae\Models\Hub;
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

        if ($this->filled('campus')) {
            $campus = Campus::findByName($this->input('campus'));
            $centers = $campus->hubs()->distinct('center')->pluck('center')->toArray();
            $hubs = $campus->hubs()->pluck('name')->toArray();
            $filters['hubs'] = array_merge($centers, $hubs);
        }

        if ($this->filled('center')) {
            $center = $this->input('center');
            $hubs = Hub::where('center', $center)->pluck('name')->toArray();
            $filters['hubs'] = array_merge([$center], $hubs);
        }

        if ($this->filled('hub'))
            $filters['hubs'] = [ $this->input('hub') ];
        else if ($this->filled('hubs'))
            $filters['hubs'] = explode(', ', $this->input('hubs'));

        return $filters;
    }

    public function dateRange()
    {
        if (!$this->filled('date')) {
            return null;
        }

        $date = $this->input('date');
        if ($this->filled('time')) {
            $dateTimeString = $date . ' ' . substr($this->input('time'), 0, 5);
            $dateMin = Carbon::createFromFormat('Y-m-d H:i', $dateTimeString);
        } else {
            $dateMin = Carbon::createFromFormat('Y-m-d', $date)->setTime(0, 0, 0);
        }

        $dateMax = $dateMin->copy()->setTime(23,59,59);

        return [$dateMin, $dateMax];
    }
}
