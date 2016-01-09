<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use SoftDeletes;

    protected $hidden = ['pivot'];

    public function users() {
        return $this->belongsToMany('App\User')->withPivot('status', 'feedback')->withTimestamps();
    }
}