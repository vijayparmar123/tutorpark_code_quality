<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class ComprehensiveQuestion extends Eloquent
{
    protected $fillable = [
        'comprehension_id','question',
    ];

    public function comprehension(){
        return $this->belongsTo(QuestionBank::class,'comprehension_id');
    }

    public function answer(){
        return $this->hasOne(ComprehensiveAnswer::class,'question_id');
    }
}
