<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class QuestionBankAnswer extends Eloquent
{
    protected $fillable = [
        'question_id','answer'
    ];
    
    public function question()
    {
      return $this->belongsTo(QuestionBankAnswer::class);
    }
}
