<?php

namespace Caronae\Http\Controllers;

use Caronae\Models\Hub;
use Caronae\Models\Neighborhood;

class PlaceController extends Controller
{
	public function index()
	{
		return [
			'zones' => $this->getZones(),
			'centers' => $this->getCenters()
		];
	}

	private function getZones()
    {
        $zones = Neighborhood::select('zone')->distinct()->pluck('zone');
        return $zones->map(function($zone) {
            return [
                'name' => $zone,
                'neighborhoods' => Neighborhood::where('zone', $zone)->pluck('name')
            ];
        });
    }

    private function getCenters()
    {
        $centers = Hub::select('center', 'campus')->distinct()->get();
        return $centers->map(function($center) {
            return [
                'name' => $center->center,
                'campus' => $center->campus,
                'hubs' => Hub::where('center', $center->center)->pluck('name')
            ];
        });
    }
}