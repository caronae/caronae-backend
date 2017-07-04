<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    use CrudTrait;

    protected $fillable = ['name', 'zone', 'distance'];
    public $timestamps = false;
    public $hidden = ['id', 'distance'];
}