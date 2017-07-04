<?php

namespace Caronae\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Hub extends Model
{
    use CrudTrait;

    protected $fillable = ['name', 'center', 'campus'];
    public $timestamps = false;
    public $hidden = ['id'];
}
