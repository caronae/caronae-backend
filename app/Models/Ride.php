<?php

namespace Caronae\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use SoftDeletes;

    protected $hidden = ['pivot', 'created_at', 'deleted_at', 'updated_at', 'date'];
    protected $dates = ['date', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['mydate', 'mytime'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('status', 'feedback')->withTimestamps();
    }

    public function driver()
    {
        return $this->belongsToMany(User::class)->wherePivot('status', 'driver')->first();
    }

    public function riders()
    {
        return $this->belongsToMany(User::class)->wherePivot('status', 'accepted')->get();
    }

    public function getMyDateAttribute()
    {
        return $this->date->format('Y-m-d');
    }

    public function getMyTimeAttribute()
    {
        return $this->date->format('H:i:s');
    }

    private static function userStats($periodStart, $periodEnd)
    {
        return DB::table('users')
            ->join('ride_user', function($join){
                $join->on('ride_user.user_id', '=', 'users.id');
            })
            ->join('rides', function($join){
                $join->on('ride_user.ride_id', '=', 'rides.id');
            })
            ->leftJoin('neighborhoods', function($join){
                $join->on('rides.myzone', '=', 'neighborhoods.zone');
                $join->on('rides.neighborhood', '=', 'neighborhoods.name');
            })
            ->where('ride_user.status', '=', 'driver')
            ->where('done', '=', true)
            ->where('rides.date', '>=', $periodStart)
            ->where('rides.date', '<=', $periodEnd)

            ->groupBy('users.id')

            ->select(
                'users.id',
                DB::raw('SUM(distance) as distancia_total'),
                DB::raw('COUNT(*) as numero_de_caronas'),
                DB::raw('(SUM(distance) / COUNT(distance)) as distancia_media')
            );
    }

    public static function getInPeriodWithUserInfo(Carbon $periodStart, Carbon $periodEnd)
    {
        $join = self::userStats($periodStart, $periodEnd);

        return Ride::leftJoin('neighborhoods', function($join){
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
            ->where('rides.date', '>=', $periodStart)
            ->where('rides.date', '<=', $periodEnd)
            ->select('users.name as driver', 'users.course', 'rides.id', 'date', 'myzone', 'neighborhood', 'going', 'hub', 'distance',
                't1.distancia_total',
                't1.numero_de_caronas',
                't1.distancia_media'
            )
            ->orderBy('date', 'DESC')
            ->get();
    }
}