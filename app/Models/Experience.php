<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Experience extends Model
{
    protected $fillable = [
        'organization',
        'designation',
        'experience_month'
    ];
}
