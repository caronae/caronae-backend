<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use CrudTrait;

    protected $table = 'campi';
    protected $fillable = ['name', 'color'];

    public function institution()
    {
        return $this->belongsTo(Institution::class)->first();
    }

    public function hubs()
    {
        return $this->hasMany(Hub::class);
    }

    public function getFullNameAttribute()
    {
        return $this->institution()->name . ' - ' . $this->name;
    }
}
