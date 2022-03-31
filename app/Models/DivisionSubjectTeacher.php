<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DivisionSubjectTeacher extends Eloquent
{
    protected $fillable = [
        'subject_id', 'teacher_id', 'created_by', 'is_class_teacher', 'is_ass_class_teacher','status'
    ];

    protected static function booted()
    {
        static::creating(function ($divisionTeacher) {
                $divisionTeacher->status = 1;
        });
    }

    public function division(){
        return $this->belongsTo(ClassDivision::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class,'subject_id');
    }

    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
}
