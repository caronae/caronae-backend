<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use CrudTrait;

    const DEFAULT_COLOR = '#000000';

    protected $fillable = ['name', 'color'];
    public $timestamps = false;

    public static function findByName($name)
    {
        return self::where('name', $name)->first();
    }

    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }

    public function getColorAttribute($value)
    {
        return $value ?: self::DEFAULT_COLOR;
    }
}
