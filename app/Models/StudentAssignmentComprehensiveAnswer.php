<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignmentComprehensiveAnswer extends Eloquent
{
    protected $fillable = [
        'answer'
    ];
}
