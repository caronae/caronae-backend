<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use CrudTrait;

    const DEFAULT_COLOR = '#000000';

    protected $table = 'campi';
    protected $fillable = ['name', 'color', 'institution_id'];

    public function institution()
    {
        return $this->belongsTo(Institution::class)->first();
    }

    public function hubs()
    {
        return $this->hasMany(Hub::class);
    }

    public function getColorAttribute($value)
    {
        return $value ?: self::DEFAULT_COLOR;
    }

    public function getFullNameAttribute()
    {
        return $this->institution()->name . ' - ' . $this->name;
    }

    public static function findByName($name)
    {
        return self::where('name', $name)->first();
    }
}
