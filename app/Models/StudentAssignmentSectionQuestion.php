<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignmentSectionQuestion extends Eloquent
{
    protected $fillable = [
        'assignment_section_question_id','obtained_mark', 
    ];

    public function studentAssignmentSection(){
        return $this->belongsTo(StudentAssignmentSection::class);
    }
	
	public function AssignmentQuestion(){
        return $this->belongsTo(SectionQuestion::class,'assignment_section_question_id');
    }

    public function studentAssignmentSectionQuestionAnswer(){
        return $this->hasOne(StudentAssignmentQuestionAnswer::class);
    }

    public function studentAssignmentComprehensiveQuestion(){
        return $this->hasMany(StudentAssignmentComprehensiveQuestion::class);
    }

    public function studentAssignmentSectionQuestionOptions(){
        return $this->hasMany(StudentAssignmentSectionQuestionOptions::class,'student_assignment_section_question_id');
    }
}
