<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Scopes\MySchoolData;

class ClassDivision extends Eloquent
{
    protected $fillable = [
        'division_id', 'descrption', 'class_teacher', 'class_teacher_subject', 'ass_class_teacher', 'ass_class_teacher_subject', 'image', 'created_by'
    ];

    protected static function booted()
    {  
        static::addGlobalScope(new MySchoolData);
    }

    protected $appends = ['name'];

    public function class(){
        return $this->belongsTo(SchoolClass::class,'school_class_id');
    }
    
    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function classTeacher(){
        return $this->belongsTo(User::class,'class_teacher');
    }

    public function aasClassTeacher(){
        return $this->belongsTo(User::class,'ass_class_teacher');
    }

    public function getNameAttribute()
    {
        return $this->division->name;
    }

    public function SubjectTeacher(){
        return $this->hasMany(DivisionSubjectTeacher::class);
    }

    public function subjects(){
        return $this->hasMany(DivisionSubjectTeacher::class,'class_division_id');
    }

    public function teachers(){
        return $this->hasMany(DivisionSubjectTeacher::class,'teacher_id');
    }

    public function students(){
        return $this->hasMany(DivisionStudent::class);
    }

    public function schedule(){
        return $this->hasMany(DivisionSchedule::class);
    }

    public function attendance(){
        return $this->hasMany(DivisionAttendance::class);
    }

    public function subjectLeaders(){
        return $this->hasMany(DivisionSubjectLeader::class);
    }

    public function todayAttendance(){
        return $this->attendance()->where(['date' => date('Y-m-d')])->groupBy('student_id','date','status');
    }
}
