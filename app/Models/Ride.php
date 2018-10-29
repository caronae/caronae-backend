<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Ride extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CrudTrait;

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
        return $this->belongsToMany(User::class)->wherePivot('status', 'accepted');
    }

    public function requests()
    {
        return $this->belongsToMany(User::class)->wherePivot('status', 'pending');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function routine()
    {
        return $this->belongsTo(self::class);
    }

    public function getMyDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d') : null;
    }

    public function getMyTimeAttribute()
    {
        return $this->date ? $this->date->format('H:i:s') : null;
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

    public function getOriginAttribute()
    {
        return $this->going ? $this->hub : $this->neighborhood;
    }

    public function getDestinationAttribute()
    {
        return $this->going ? $this->neighborhood : $this->hub;
    }

    public function availableSlots()
    {
        $ridersCount = $this->belongsToMany(User::class)->wherePivot('status', 'accepted')->count();

        return $this->slots - $ridersCount;
    }

    public function isAroundDate($date)
    {
        return abs($date->diffInMinutes($this->date)) <= 30;
    }

    public function scopeWithAvailableSlots($query)
    {
        return $query
            ->leftjoin('ride_user', 'rides.id', '=', 'ride_user.ride_id')
            ->select('rides.*')
            ->whereIn('ride_user.status', ['pending', 'accepted', 'driver'])
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
            } elseif ($key == 'hubs') {
                $query = $query->where('rides.hub', 'SIMILAR TO', '(' . implode('|', $value) . ')%');
            } else {
                $query = $query->where('rides.' . $key, $value);
            }
        });

        return $query;
    }

    public function scopeWithInstitution($query, $institution)
    {
        return $query
            ->whereHas('users', function ($query) use ($institution) {
                $query->where('status', 'driver')->where('institution_id', $institution->id);
            });
    }
}
