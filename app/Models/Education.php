<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Education extends Model
{
    protected $fillable = [
            'degree',
            'college',
            'place'
        ];
}
