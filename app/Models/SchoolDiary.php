<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Scopes\MySchoolData;

class SchoolDiary extends Eloquent
{
    protected $fillable = [
        'user_id', 'division_id', 'date', 'created_by'
    ];

    // Global scope to get only my school data
    protected static function booted()
    {  
        static::addGlobalScope(new MySchoolData);
    }

    public function division(){
        return $this->belongsTo(ClassDivision::class,'division_id');
    }

    public function details(){
        return $this->hasOne(SchoolDiarySubject::class);
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function linkTimeline()
    {
    return $this->morphMany(Timeline::class, 'linkable');
    }
}
