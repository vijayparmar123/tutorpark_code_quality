<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class QuestionBank extends Eloquent
{
    protected $fillable = [
        'syllabus_id','class_id','subject_id','type','question','created_by'
    ];

    public function answer()
    {
        return $this->hasOne(QuestionBankAnswer::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function syllabus(){
        return $this->belongsTo(Syllabus::class);
    }

    public function class(){
        return $this->belongsTo(TpClass::class);
    }

    public function type(){
        return $this->belongsTo(QuestionType::class,'question_type_id');
    }

    public function options(){
        return $this->hasMany(QuestionOption::class);
    }

    public function comprehensiveQuestions(){
        return $this->hasMany(ComprehensiveQuestion::class,'comprehension_id');
    }
}
