<?php

namespace Caronae\Http\Controllers\API\v1;

use Caronae\Http\Controllers\BaseController;
use Caronae\Http\Resources\InstitutionResource;
use Caronae\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlaceController extends BaseController
{
    const CACHE_TIME_MINUTES = 60 * 24 * 365;

    public function index(Request $request)
    {
        return [
            'zones' => $this->getZones(),
            'campi' => $this->getCampi($request),
            'institution' => $this->getInstitution($request),
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

    private function getCampi(Request $request)
    {
        $institution = $request->user()->institution;
        return Cache::remember('campi_institution_' . $institution->id, self::CACHE_TIME_MINUTES, function () use ($institution) {
            Log::info("Loading campi for {$institution->name} from database.");
            $campi = $institution->campi;
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

    private function getInstitution($request)
    {
        return new InstitutionResource($request->user()->institution);
    }
}