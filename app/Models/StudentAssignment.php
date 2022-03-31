<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignment extends Eloquent
{

    protected $fillable = [
        'student_id', 'student_status', 'tutor_status', 'attempted_date','obtained_mark'
    ];

    protected $dates = [
        "attempted_date",
    ];

    protected static function booted()
    {
        static::creating(function ($assignment) {
            $assignment->student_status = 'pending';
            $assignment->tutor_status = 'pending';
            $assignment->obtained_mark = '';
            $assignment->created_by = auth()->user()->id;
        });
    }

    public function student(){
        return $this->belongsTo(User::class,'student_id');
    }

    public function assignment(){
        return $this->belongsTo(Assignment::class,'assignment_id');
    }

    public function studentAssignmentSection(){
        return $this->hasMany(StudentAssignmentSection::class);
    }
}
