<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
// use Illuminate\Database\Eloquent\Model;

class Topic extends Eloquent
{
    protected $fillable = [
        'name', 'key_points', 'assignment_id', 'external_urls', 'description', 'subject_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function library()
    {
        return $this->hasOne(Library::class);
    }

    public function course()
    {
        return $this->hasOne(Course::class);
    }

    // public function userdetils()
    // {
    //     return $this->hasOne(UserDetails::class);
    // }

    public function tutorsCanTeach()
    {
        return $this->belongsToMany(UserDetails::class, null, 'preferred_topics', 'tutors_can_teach');
    }
}
