<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model 
{
    protected $table = 'users';

    public function rides() {
        return $this->belongToMany('App\Ride');
    }
}
