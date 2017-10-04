<?php

namespace Caronae\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Ride extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = ['myzone', 'neighborhood', 'place', 'route', 'slots', 'hub', 'description', 'going', 'date'];
    protected $hidden = ['pivot', 'created_at', 'deleted_at', 'updated_at', 'date', 'done'];
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

    public function institution()
    {
        return $this->driver()->institution();
    }

    public function riders()
    {
        return $this->belongsToMany(User::class)->wherePivot('status', 'accepted')->get();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getMyDateAttribute()
    {
        return $this->date->format('Y-m-d');
    }

    public function getMyTimeAttribute()
    {
        return $this->date->format('H:i:s');
    }

    public function getTitleAttribute()
    {
        if ($this->going) {
            $route = $this->neighborhood . ' → ' . $this->hub;
        } else {
            $route = $this->hub . ' → ' . $this->neighborhood;
        }
        return $route . ' | ' . $this->date->format('d/m');
    }

    public function getAvailableSlotsAttribute()
    {
        $ridersCount = $this->belongsToMany(User::class)->wherePivot('status', 'accepted')->count();
        return $this->slots - $ridersCount;
    }

    public function scopeWithAvailableSlots($query)
    {
        return $query
            ->leftjoin('ride_user', 'rides.id', '=', 'ride_user.ride_id')
            ->select('rides.*')
            ->whereIn('ride_user.status', ['pending','accepted','driver'])
            ->groupBy('rides.id')
            ->having(DB::raw('count(ride_user.user_id)-1'), '<', DB::raw('rides.slots'));
    }

    public function scopeFinished($query)
    {
        return $query->where('rides.done', 'true');
    }

    public function scopeNotFinished($query)
    {
        return $query->where('rides.done', 'false');
    }

    public function scopeInTheFuture($query)
    {
        return $query->where('rides.date', '>=', Carbon::now());
    }

    public function scopeWithFilters($query, array $filters = [])
    {
        collect($filters)->each(function ($value, $key) use (&$query) {
            if ($key == 'neighborhoods') {
                $query = $query->whereIn('rides.neighborhood', $value);
            } else if ($key == 'hubs') {
                $query = $query->where('rides.hub', 'SIMILAR TO', '(' . implode('|', $value) . ')%');
            } else {
                $query = $query->where('rides.' . $key, $value);
            }
        });

        return $query;
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