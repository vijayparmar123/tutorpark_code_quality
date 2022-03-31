<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class CourseMessage extends Model
{
    protected $fillable = ['course_id','sender_id','message','read_at'];

    protected $dates = ['read_at'];

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
