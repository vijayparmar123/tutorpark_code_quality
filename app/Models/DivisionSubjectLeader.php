<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DivisionSubjectLeader extends Eloquent
{
    protected $fillable = [
        'class_division_id','subject_id','leader_id','status','created_by'
    ];

    public function leader(){
        return $this->belongsTo(User::class,'leader_id');
    }

    public function division(){
        return $this->belongsTo(ClassDivision::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class,'subject_id');
    }
}
