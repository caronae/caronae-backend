<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class User extends Model 
{
    use SoftDeletes;

	protected $hidden = ['token', 'gcm_token', 'pivot', 'id_ufrj'];
    protected $dates = ['deleted_at'];

    public function rides() {
        return $this->belongsToMany('App\Ride')->withPivot('status', 'feedback')->withTimestamps();
    }

    public function banish(){
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

    public function unban(){
        $this->restore();
    }
}
