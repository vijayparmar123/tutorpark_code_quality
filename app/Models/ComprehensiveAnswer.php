<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class ComprehensiveAnswer extends Eloquent
{
    protected $fillable = [
        'question_id','answer',
    ];

    public function question(){
        return $this->belongsTo(ComprehensiveQuestion::class,'question_id');
    }
}
