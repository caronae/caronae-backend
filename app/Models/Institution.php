<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use CrudTrait;
    
    protected $fillable = [
        'name',
        'slug',
        'password',
        'authentication_url',
        'going_label',
        'leaving_label',
        'login_message',
    ];

    public static function create(array $attributes = [])
    {
        $attributes['password'] = bcrypt($attributes['name'] . time());
        $model = static::query()->create($attributes);
        return $model;
    }

    public function campi()
    {
        return $this->hasMany(Campus::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

}
