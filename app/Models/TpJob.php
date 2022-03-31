<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class TpJob extends Model
{
    protected $fillable = [
        'syllabus_id',
        'class_id',
        'subject_id',
        'mode',
        'type',
        'topic',
        'requirements',
        'start_time',
        'end_time'
    ];

    public function author()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function class()
    {
        return $this->belongsTo(TpClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function isCity($city)
    {
        return strtolower($this->author->details->city) == strtolower($city);
    }

    public function isGender($gender)
    {
        return strtolower($this->author->details->gender) == strtolower($gender);
    }
}