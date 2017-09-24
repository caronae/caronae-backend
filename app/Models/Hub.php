<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Hub extends Model
{
    use CrudTrait;

    protected $fillable = ['name', 'center', 'campus_id'];
    public $timestamps = false;
    public $hidden = ['id'];

    public function scopeWithCampus($query, string $campus)
    {
        return $query->where('campus', $campus);
    }

    public static function findByName(string $name)
    {
        return self::where('name', $name)->first();
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
