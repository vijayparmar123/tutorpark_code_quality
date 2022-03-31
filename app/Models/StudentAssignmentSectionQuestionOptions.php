<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignmentSectionQuestionOptions extends Eloquent
{
    protected $fillable = [
        'question_option_id','type','matching_key','key'
    ];

    public function matching()
    {
        return $this->belongsTo(StudentAssignmentSectionQuestionOptions::class, 'matching_id');
    }

    public function QuestionOption(){
        return $this->belongsTo(QuestionOption::class,'question_option_id');
    }
}
