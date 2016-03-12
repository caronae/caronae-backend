<?php

namespace App\Services;

use Carbon\Carbon;
use DB;

class RankingService extends Service
{
    public function getUsersOrderedByBestFeedbackInPeriod(Carbon $periodStart, Carbon $periodEnd)
    {
        $sub = $this->baseQuery($periodStart, $periodEnd)
            ->where('ride_user.status', '=', 'driver')

            ->select(
                "users.id",
                "users.name",
                "users.profile",
                "users.course",
                DB::raw("(SELECT COUNT(*) FROM ride_user WHERE ride_id = rides.id AND status = 'accepted') as caronistas"),
                DB::raw("(SELECT COUNT(*) FROM ride_user WHERE ride_id = rides.id AND status = 'accepted' AND feedback = 'good') as feedback_positivo"),
                DB::raw("(SELECT COUNT(*) FROM ride_user WHERE ride_id = rides.id AND status = 'accepted' AND feedback = 'bad') as feedback_negativo")
            );

        return DB::table(DB::raw('('.$sub->toSQL().') as t1'))
            ->mergeBindings($sub) // isso não é documentado pelo Laravel. É necessário para a subquery funcionar
            ->groupBy('id', 'name', 'course', 'profile')
            ->orderBy('reputacao', 'desc')
            ->select(
                'name',
                'course',
                'profile',
                DB::raw('COUNT(*) as caronas'),
                DB::raw('SUM(caronistas) as caronistas'),
                DB::raw('SUM(feedback_positivo) as feedback_positivo'),
                DB::raw('SUM(feedback_negativo) as feedback_negativo'),
                DB::raw('SUM(caronistas) - SUM(feedback_positivo) - SUM(feedback_negativo) as sem_feedback'),
                // esse NULLIF e COALESCE servem para evitar um erro de divisão por zero
                // ver: http://stackoverflow.com/a/8726609
                // Se ele não tiver recebido nenhuma avaliação ou não tiver dado carona, sua reputação será 0
                DB::raw('COALESCE( SUM(feedback_positivo) / NULLIF( SUM(feedback_positivo)+SUM(feedback_negativo), 0), 0) as reputacao')
            )->get();
    }

    public function getUsersOrderedByRidesInPeriod($periodStart, $periodEnd)
    {
        return $this->baseQuery($periodStart, $periodEnd)
            ->where('ride_user.status', '=', 'accepted')

            ->groupBy('users.id', 'users.name', 'users.course', 'users.profile')
            ->orderBy('caronas', 'desc')

            ->select(
                "users.id",
                "users.name",
                "users.profile",
                "users.course",
                DB::raw('COUNT(*) as caronas')
            )->get();
    }

    public function getDriversOrderedByRidesInPeriod($periodStart, $periodEnd)
    {
        return $this->baseQuery($periodStart, $periodEnd)
            ->leftJoin('neighborhoods', function($join){
                $join->on('rides.myzone', '=', 'neighborhoods.zone');
                $join->on('rides.neighborhood', '=', 'neighborhoods.name');
            })

            ->where('rides.mydate', '>=', $this->whenUserBecameADriver())

            ->where('ride_user.status', '=', 'accepted')

            ->groupBy('users.id', 'users.name', 'users.course', 'users.profile')
            ->orderBy('caronas', 'desc')

            ->select(
                "users.id",
                "users.name",
                "users.profile",
                "users.course",
                DB::raw('COUNT(*) as caronas'),
                // 131 é um valor mágico. É a taxa media de carbono emitido por um carro no Brasil
                DB::raw('SUM(neighborhoods.distance * 131) as carbono_economizado')
            )->get();
    }

    public function getDriversOrderedByAverageOccupancyInPeriod($periodStart, $periodEnd)
    {
        $sub = $this->baseQuery($periodStart, $periodEnd)
            ->where('ride_user.status', '=', 'driver')
            ->select(
                "users.id",
                "users.name",
                "users.profile",
                "users.course",
                DB::raw("(SELECT COUNT(*) FROM ride_user WHERE ride_id = rides.id AND status = 'accepted') as caronistas")
            );

        return DB::table(DB::raw('('.$sub->toSQL().') as t1'))
            ->mergeBindings($sub)
            ->groupBy('id', 'name', 'course', 'profile')
            ->orderBy('media', 'desc')
            ->select(
                'name',
                'course',
                'profile',
                DB::raw('COUNT(*) as caronas'),
                // Essa query foi feita porque a versão do Postgres do servidor da TIC era antiga
                // e não possuia a função de Moda nativamente. Essa é a implementação de moda
                // recomendada pelo Postgres aqui: https://wiki.postgresql.org/wiki/Aggregate_Mode#mode.28.29_for_Postgres_9.3_or_earlier_.28superseded_in_9.4.29
                // e adaptada para o caso específico dessa query.
                DB::raw('(select * from unnest(array_agg(caronistas)) as t group by t order by count(*) desc limit 1) as moda'),
                DB::raw('round(AVG(caronistas), 2) as media')
            )->get();
    }
}