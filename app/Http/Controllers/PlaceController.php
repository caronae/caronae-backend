<?php

namespace Caronae\Http\Controllers;

use Caronae\Models\Hub;
use Caronae\Models\Neighborhood;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlaceController extends Controller
{
    const CACHE_TIME_MINUTES = 60*24*365;

	public function index()
	{
		return [
			'zones' => $this->getZones(),
			'centers' => $this->getCenters()
		];
	}

	public function getZones()
    {
        return Cache::remember('zones', self::CACHE_TIME_MINUTES, function () {
            Log::info('Loading zones from database');
            $zones = Neighborhood::select('zone')->distinct()->pluck('zone');
            return $zones->map(function($zone) {
                return [
                    'name' => $zone,
                    'neighborhoods' => Neighborhood::where('zone', $zone)->pluck('name')
                ];
            });
        });
    }

    private function getCenters()
    {
        return Cache::remember('centers', self::CACHE_TIME_MINUTES, function () {
            Log::info('Loading centers from database');
            $centers = Hub::select('center', 'campus')->distinct()->get();
            return $centers->map(function ($center) {
                return [
                    'name' => $center->center,
                    'campus' => $center->campus,
                    'hubs' => Hub::where('center', $center->center)->pluck('name')
                ];
            });
        });
    }
}