<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Module extends Eloquent
{
    protected $fillable = [
        'name'
    ];
}
