<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model 
{
    use SoftDeletes;

	protected $hidden = ['token', 'gcm_token'];
	
    public function rides() {
        return $this->belongsToMany('App\Ride')->withPivot('status')->withTimestamps();
    }
}
