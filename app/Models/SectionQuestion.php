<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SectionQuestion extends Eloquent
{
    protected $fillable = [
        'question_id','mark'
    ];
    
    public function question(){
        return $this->belongsTo(QuestionBank::class,'question_id');
    }
}
