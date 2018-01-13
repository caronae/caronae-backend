<?php

namespace Caronae\Http\Controllers\API\v1;

use Caronae\Http\Controllers\BaseController;
use Caronae\Models\Campus;
use Caronae\Models\Zone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlaceController extends BaseController
{
    const CACHE_TIME_MINUTES = 60 * 24 * 365;

    public function index()
    {
        return [
            'zones' => $this->getZones(),
            'campi' => $this->getCampi(),
        ];
    }

    public function getZones()
    {
        return Cache::remember('zones', self::CACHE_TIME_MINUTES, function () {
            Log::info('Loading zones from database.');
            $zones = Zone::all();
            return $zones->map(function ($zone) {
                return [
                    'name' => $zone->name,
                    'color' => $zone->color,
                    'neighborhoods' => $zone->neighborhoods()->pluck('name'),
                ];
            });
        });
    }

    private function getCampi()
    {
        return Cache::remember('campi', self::CACHE_TIME_MINUTES, function () {
            Log::info('Loading campi from database.');
            $campi = Campus::all();
            return $campi->filter(function ($campus) {
                return $campus->hubs()->count() > 0;
            })->map(function ($campus) {
                return [
                    'name' => $campus->name,
                    'color' => $campus->color,
                    'centers' => $campus->hubs()->distinct('center')->pluck('center'),
                    'hubs' => $campus->hubs()->pluck('name'),
                ];
            });
        });
    }
}