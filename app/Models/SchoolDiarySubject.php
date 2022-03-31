<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SchoolDiarySubject extends Eloquent
{
    protected $fillable = [
        'subject_id', 'class_work', 'class_work_attachment', 'home_work','home_work_attachment', 'tomorrow_topics'
    ];

    public function subject(){
        return $this->belongsTo(Subject::class,'subject_id');
    }

    public function diary(){
        return $this->belongsTo(SchoolDiary::class,'school_diary_id');
    }
}
