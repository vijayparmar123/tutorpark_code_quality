<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignmentComprehensiveQuestion extends Eloquent
{
    protected $fillable = [
        'comprehensive_question_id', 'obtained_mark'
    ];
    
    public function studentAssignmentSectionQuestion(){
        return $this->belongsTo(StudentAssignmentSectionQuestion::class);
    }

    public function studentAssignmentComprehensiveAnswer(){
        return $this->hasMany(StudentAssignmentComprehensiveAnswer::class);
    }

    public function comprehensiveQuestion(){
        return $this->belongsTo(comprehensiveQuestion::class,'comprehensive_question_id');
    }
}
