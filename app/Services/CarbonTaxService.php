<?php

namespace App\Services;

use DB;

class CarbonTaxService extends Service
{
    public function getCarbonTaxSaved($periodStart, $periodEnd)
    {
        return $this->baseQueryWithAllUsers($periodStart, $periodEnd)
            ->leftJoin('neighborhoods', function($join){
                $join->on('rides.myzone', '=', 'neighborhoods.zone');
                $join->on('rides.neighborhood', '=', 'neighborhoods.name');
            })

            ->where('rides.mydate', '>=', $this->whenUserBecameADriver())

            ->where('ride_user.status', '=', 'accepted')

            ->select(
                // 131 é um valor mágico. É a taxa media de carbono emitido por um carro no Brasil
                DB::raw('SUM(neighborhoods.distance * 131) as carbono_economizado')
            )->get()[0]->carbono_economizado;
    }
}