<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model 
{
	protected $hidden = ['token', 'gcm_token'];
	
    public function rides() {
        return $this->belongsToMany('App\Ride')->withPivot('status')->withTimestamps();
    }
}
