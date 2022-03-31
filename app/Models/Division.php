<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Division extends Eloquent
{
    protected $fillable = [
        'name', 'tag'
    ];
}
