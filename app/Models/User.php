<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Model implements AuthenticatableContract
{
    use Notifiable;
    use SoftDeletes;
    use CrudTrait;
    use Authenticatable;

    protected $fillable = ['name', 'email', 'profile', 'course', 'id_ufrj', 'profile_pic_url', 'token', 'institution_id'];
    protected $hidden = ['token', 'pivot', 'id_ufrj', 'deleted_at', 'updated_at', 'app_platform', 'app_version', 'banned', 'institution_id'];
    protected $dates = ['deleted_at'];

    public static function findByInstitutionId($id)
    {
        return self::where('id_ufrj', $id)->first();
    }

    public function setCarPlateAttribute($value)
    {
        $this->attributes['car_plate'] = strtoupper($value);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function rides()
    {
        return $this->belongsToMany(Ride::class)
            ->withPivot('status', 'feedback')
            ->withTimestamps();
    }

    public function activeRides()
    {
        return $this->belongsToMany(Ride::class)
            ->wherePivotIn('status', ['driver', 'accepted'])
            ->notFinished();
    }

    public function offeredRides()
    {
        return $this->belongsToMany(Ride::class)
            ->wherePivot('status', 'driver');
    }

    public function availableRides()
    {
        return $this->offeredRides()
            ->notFinished()
            ->inTheFuture();
    }

    public function pendingRides()
    {
        return $this->belongsToMany(Ride::class)
            ->wherePivot('status', 'pending')
            ->notFinished()
            ->inTheFuture();
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

            $this->banned = true;
            $this->save();
        });
    }

    public function unban()
    {
        $this->banned = false;
        $this->save();
    }

    public function generateToken()
    {
        $this->token = strtoupper(substr(base_convert(sha1(uniqid() . rand()), 16, 36), 0, 6));
        return $this;
    }
}
