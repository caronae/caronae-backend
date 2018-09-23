<?php

namespace Caronae\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use Encryptable;
    use SoftDeletes;

    protected $fillable = ['ride_id', 'user_id', 'body', 'created_at'];
    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];
    protected $encryptable = ['body'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['date'];

    protected $rules = [
        'body' => 'required',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDateAttribute()
    {
    	return $this->created_at;
    }
}
