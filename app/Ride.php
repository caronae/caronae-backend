<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use SoftDeletes;

    protected $hidden = ['pivot'];
    protected $dates = ['deleted_at'];

    public function users() {
        return $this->belongsToMany('App\User')->withPivot('status', 'feedback')->withTimestamps();
    }

    private static function userStats($periodStart, $periodEnd){
        return DB::table('users')
            ->join('ride_user', function($join){
                $join->on('ride_user.user_id', '=', 'users.id');
            })
            ->join('rides', function($join){
                $join->on('ride_user.ride_id', '=', 'rides.id');
            })
            ->join('neighborhoods', function($join){
                $join->on('rides.myzone', '=', 'neighborhoods.zone');
                $join->on('rides.neighborhood', '=', 'neighborhoods.name');
            })
            ->where('ride_user.status', '=', 'driver')
            ->where('done', '=', true)
            ->where('rides.mydate', '>=', $periodStart)
            ->where('rides.mydate', '<=', $periodEnd)

            ->groupBy('users.id')

            ->select(
                'users.id',
                DB::raw('SUM(distance) as distancia_total'),
                DB::raw('COUNT(*) as numero_de_caronas'),
                DB::raw('(SUM(distance) / COUNT(*)) as distancia_media')
            );
    }

    public static function getInPeriodWithUserInfo(Carbon $periodStart, Carbon $periodEnd){
        $join = self::userStats($periodStart, $periodEnd);

        return Ride::join('neighborhoods', function($join){
            $join->on('rides.myzone', '=', 'neighborhoods.zone');
            $join->on('rides.neighborhood', '=', 'neighborhoods.name');
        })
            ->join('ride_user', function($join){
                $join->on('ride_user.ride_id', '=', 'rides.id');
            })
            ->join('users', function($join){
                $join->on('ride_user.user_id', '=', 'users.id');
            })
            ->join(DB::raw('(' . $join->toSql() . ') as t1'), function($join){
                $join->on('users.id' , '=', 't1.id');
            })
            ->mergeBindings($join)
            ->where('ride_user.status', '=', 'driver')
            ->where('done', '=', true)
            ->where('rides.mydate', '>=', $periodStart)
            ->where('rides.mydate', '<=', $periodEnd)
            ->select('users.name as driver', 'users.course', 'rides.id', 'mydate', 'mytime', 'myzone', 'neighborhood', 'going', 'hub', 'distance',
                't1.distancia_total',
                't1.numero_de_caronas',
                't1.distancia_media'
            )
            ->orderBy('mydate', 'DESC')
            ->orderBy('mytime', 'DESC')
            ->get();
    }
}