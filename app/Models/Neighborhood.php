<?php

namespace Caronae\Models;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    public $timestamps = false;
    public $hidden = [ 'distance' ];
}