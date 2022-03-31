<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class QuestionOption extends Eloquent
{
    protected $fillable = [
        'name','type'
    ];

    public function question(){
        return $this->belongsTo(QuestionBank::class);
    }

    public function matching()
    {
        return $this->belongsTo(QuestionOption::class, 'matching_id');
    }
}
