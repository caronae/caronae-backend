<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;
    use CrudTrait;

    protected $fillable = [
        'name',
        'email',
        'profile',
        'course',
        'id_ufrj',
        'profile_pic_url',
        'token',
        'institution_id',
        'phone_number',
        'location',
        'email',
        'car_owner',
        'car_model',
        'car_color',
        'car_plate',
        'face_id',
    ];

    protected $hidden = [
        'token',
        'pivot',
        'id_ufrj',
        'deleted_at',
        'updated_at',
        'app_platform',
        'app_version',
        'banned',
        'institution_id',
    ];

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
            ->has('riders')
            ->notFinished();
    }

    public function offeredRides()
    {
        return $this->belongsToMany(Ride::class)
            ->wherePivot('status', 'driver');
    }

    public function acceptedRides()
    {
        return $this->belongsToMany(Ride::class)
            ->wherePivot('status', 'accepted');
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
