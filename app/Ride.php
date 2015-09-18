<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model {
    public function users() {
        return $this->belongToMany('App\User');
    }
}