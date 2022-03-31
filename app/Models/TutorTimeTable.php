<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TutorTimetable extends Eloquent
{
    protected $fillable = [
        'start_time',
        'end_time',
        'day',
        'times',
        'mode',
        'teaching_mode',
        'user_id'
    ];

    protected $dates = [
        'start_time',
        'end_time',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class);
    }

    // public function tution()
    // {
    //     return $this->belongsTo(Tutions::class);
    // }

}