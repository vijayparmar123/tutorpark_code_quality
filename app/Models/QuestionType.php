<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class QuestionType extends Eloquent
{
    protected $fillable = [
        'title','tag'
    ];

    public function questions(){
        return $this->hasMany(QuestionBank::class,'question_type_id');
    }
}
