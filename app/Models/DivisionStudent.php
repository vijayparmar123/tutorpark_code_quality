<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DivisionStudent extends Eloquent
{
    protected $fillable = [
        'student_id','status','is_leader'
    ];

    protected static function booted()
    {
        static::creating(function ($divisionStudent) {
                $divisionStudent->status = 1;
        });
    }

    public function student(){
        return $this->belongsTo(User::class,'student_id');
    }

    public function subjectLeadership(){
        return $this->hasMany(DivisionSubjectLeader::class,'leader_id','student_id');
    }
}
