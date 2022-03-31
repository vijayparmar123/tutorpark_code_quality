<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Sessions extends Eloquent
{
    protected $fillable = [
        'title','tutor_id','tuition_id', 'day', 'start_time', 'end_time', 'date', 'attended_student_id', 'number_of_hours', 'attendance', 'meeting_link','is_completed','completed_at', 'meeting_id','attendance_taken'
    ];

    protected $dates = [
        'start_time',
        'end_time',
        'date',
        'completed_at'
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class,'tutor_id');
    }

    public function tuition()
    {
        return $this->belongsTo(Tuition::class);
    }
	
	public function attendance()
    {
        return $this->hasMany(SessionAttendance::class);
    }
}
