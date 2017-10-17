<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    use CrudTrait;

    protected $fillable = ['name', 'zone_id', 'distance'];
    public $timestamps = false;
    public $hidden = ['id', 'distance'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}