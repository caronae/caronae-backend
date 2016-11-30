<?php


namespace Caronae\Services;

use Carbon\Carbon;
use DB;

/**
 * Essa classe possui metodos em comum entre os outros servicos e é extendido por eles
 */
class Service
{
    /*
     * Possui uma base comum na qual as outras queries possam começar.
     * Ela retorna a lista de usuarios e suas corridas, já com as corridas
     * não terminadas e fora do período removidas.
     *
     * É importante lembrar que ela retorna usuarios que foram banidos.
     * Para retirá-los, use "baseQuery".
     */
    protected function baseQueryWithAllUsers(Carbon $periodStart, Carbon $periodEnd){
        return DB::table('users')
            ->leftJoin('ride_user', function($join){
                $join->on('users.id', '=', 'ride_user.user_id');

            })->leftJoin('rides', function($join)  {
                $join->on('ride_user.ride_id', '=', 'rides.id');
            })
            ->where('rides.done', '=', true)
            ->where('rides.mydate', '>=', $periodStart->format("Y-m-d"))
            ->where('rides.mydate', '<=', $periodEnd->format("Y-m-d"));
    }

    protected function baseQuery(Carbon $periodStart, Carbon $periodEnd){
        return $this->baseQueryWithAllUsers($periodStart, $periodEnd)
            ->whereNull('users.deleted_at'); // retira usuarios banidos
    }

    /*
     * Retorna a data em que o usuario se tornou motorista.
     * Foi feita para ser usada como subquery em outras queries.
     * (Ver RankingService)
     */
    protected function whenUserBecameADriver(){
        return DB::raw("
                (SELECT mydate
                 FROM users as u
                 JOIN ride_user ON users.id = ride_user.user_id
                 JOIN rides ON rides.id = ride_user.ride_id
                 WHERE u.id = users.id AND
                       ride_user.status = 'driver' AND
                       rides.done = true
                 ORDER BY mydate ASC
                 LIMIT 1
                 )");
    }
}