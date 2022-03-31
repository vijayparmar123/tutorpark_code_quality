<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SessionAttendance extends Eloquent
{
    protected $fillable = [
        'session_id', 'student_id', 'status'
    ];
	
	public function session()
    {
        return $this->belongsTo(Sessions::class);
    }
	
	public function student()
    {
        return $this->belongsTo(User::class,'student_id');
    }
}
