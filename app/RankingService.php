<?php

namespace App;

use Carbon\Carbon;
use DB;

class RankingService
{

    public function baseQuery(Carbon $periodStart, Carbon $periodEnd){
        // this method returns a common base query that all other methods use.
        return DB::table('users')
            ->leftJoin('ride_user', function($join){
                $join->on('users.id', '=', 'ride_user.user_id');

            })->leftJoin('rides', function($join)  {
                $join->on('ride_user.ride_id', '=', 'rides.id');
            })
            ->whereNull('users.deleted_at')
            ->where('rides.done', '=', true)
            ->where('rides.mydate', '>=', $periodStart->format("Y-m-d"))
            ->where('rides.mydate', '<=', $periodEnd->format("Y-m-d"));
    }

    public function getUsersOrderedByBestFeedbackInPeriod(Carbon $periodStart, Carbon $periodEnd)
    {
        /**
         * Observações:
         * - Só conta caso a ride esteja done
         * - O usuario aparece se for motorista ou se não for, mas deu carona no intervalo.
         *   esse tipo de usuario pode existir caso ele tenha desmarcado a opção de ser motorista no intervalo.
         * - O numero de caronistas é contado a partir das pessoas que aceitaram ir. Não existe forma atualmente
         *   de assegurar que a pessoa foi realmente ou de avisar que ela faltou a carona.
         * - Não esquecer de sumir com usuarios banidos!
         */
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
            ->mergeBindings($sub)
            ->groupBy('id', 'name', 'course', 'profile')
            ->orderBy('reputacao', 'desc')
            ->select(
                'name',
                'course',
                'profile',
                DB::raw('COUNT(*) as caronas'), // contar rideId ajuda a não contar motoristas sem caronas como 1. Ele será NULL nesse caso
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

            ->where('users.car_owner', '=', true)

            ->where('ride_user.status', '=', 'accepted')

            ->groupBy('users.id', 'users.name', 'users.course', 'users.profile')
            ->orderBy('caronas', 'desc')

            ->select(
                "users.id",
                "users.name",
                "users.profile",
                "users.course",
                DB::raw('COUNT(*) as caronas'),
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
                DB::raw('(select * from unnest(array_agg(caronistas)) as t group by t order by count(*) desc limit 1) as moda'),
                DB::raw('round(AVG(caronistas), 2) as media')
            )->get();
    }
}