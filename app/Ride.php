<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use SoftDeletes;

    public function users() {
        return $this->belongsToMany('App\User')->withPivot('status')->withTimestamps();
    }
}