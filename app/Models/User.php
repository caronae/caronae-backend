<?php

namespace Caronae\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'profile', 'id_ufrj', 'token'];
    protected $hidden = ['token', 'gcm_token', 'pivot', 'id_ufrj', 'deleted_at', 'updated_at', 'app_platform', 'app_version'];
    protected $dates = ['deleted_at'];

    public function rides()
    {
        return $this->belongsToMany(Ride::class)->withPivot('status', 'feedback')->withTimestamps();
    }

    public function belongsToRide(Ride $ride)
    {
        return !is_null(
            RideUser::where('ride_id', $ride->id)
                 ->where('user_id', $this->id)
                 ->whereIn('status', ['driver', 'accepted'])
                 ->first()
        );
    }

    public function ownsRide(Ride $ride)
    {
        return !is_null(
            RideUser::where('ride_id', $ride->id)
                 ->where('user_id', $this->id)
                 ->where('status', 'driver')
                 ->first()
        );
    }

    public function banish()
    {
        DB::transaction(function(){
            $ids = $this->rides()->where('done', false)->get()->pluck('id');

            RideUser::where('status', '<>', 'driver')
                ->where('user_id', $this->id)
                ->whereIn('ride_id', $ids)
                ->delete();

            $deadRides = RideUser::where('status', '=', 'driver')->whereIn('ride_id', $ids)->get()->pluck('ride_id');
            RideUser::whereIn('ride_id', $deadRides)->delete();
            Ride::whereIn('id', $deadRides)->delete();

            $this->delete();
        });
    }

    public function unban()
    {
        $this->restore();
    }

    public function usesNotificationsWithToken()
    {
        if (empty($this->gcm_token)) return false;

        if (empty($this->app_platform) || empty($this->app_version)) return true;

        return (
            ($this->app_platform == 'iOS' && preg_match('/1\.0(\.[0-2])?$/', $this->app_version))
            || ($this->app_platform == 'Android' && preg_match('/1\.0(\.[0-2])?$/', $this->app_version))
        );
    }
}
