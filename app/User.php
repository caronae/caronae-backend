<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'profile', 'id_ufrj', 'token'];
    protected $hidden = ['token', 'gcm_token', 'pivot', 'id_ufrj', 'deleted_at', 'updated_at'];
    protected $dates = ['deleted_at'];

    public function rides()
    {
        return $this->belongsToMany('App\Ride')->withPivot('status', 'feedback')->withTimestamps();
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
}
