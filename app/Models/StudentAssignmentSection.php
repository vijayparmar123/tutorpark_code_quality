<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StudentAssignmentSection extends Eloquent
{
    protected $fillable = [
        'assignment_section_id','obtained_mark'
    ];

    public function studentAssignment(){
        return $this->belongsTo(StudentAssignment::class);
    }

    public function AssignmentSection(){
        return $this->belongsTo(AssignmentSection::class);
    }

    public function studentAssignmentSectionQuestion(){
        return $this->hasMany(StudentAssignmentSectionQuestion::class);
    }
}
