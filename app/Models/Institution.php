<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use CrudTrait;
    
    protected $fillable = ['name', 'password', 'authentication_url'];

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

}
