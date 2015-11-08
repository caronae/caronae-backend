<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model 
{
    public function rides() {
        return $this->belongsToMany('App\Ride')->withPivot('status');
    }
}
