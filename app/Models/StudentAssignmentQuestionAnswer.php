<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignmentQuestionAnswer extends Eloquent
{
    protected $fillable = [
        'given_answer'
    ];

    public function studentAssignmentSectionQuestion(){
        return $this->belongsTo(StudentAssignmentSectionQuestion::class);
    }
}
