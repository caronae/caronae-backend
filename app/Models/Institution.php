<?php

namespace Caronae\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Institution extends Model
{
    use CrudTrait;
    
    protected $fillable = ['name', 'password'];

    public static function create(array $attributes = [])
    {
        $attributes['password'] = bcrypt($attributes['name'] . time());
        $model = static::query()->create($attributes);
        return $model;
    }

}
