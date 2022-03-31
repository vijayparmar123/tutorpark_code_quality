<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Scopes\MySchoolData;

class SchoolClass extends Eloquent
{
    protected $fillable = [
        'syllabus_id', 'class_id', 'class_name', 'description', 'image', 'created_by'
    ];

    // Global scope to get only my school data
    protected static function booted()
    {  
        static::addGlobalScope(new MySchoolData);
    }

    public function getNameAttribute()
    {
        return $this->class_name;
    }

    public function syllabus(){
        return $this->belongsTo(Syllabus::class);
    }

    public function class(){
        return $this->belongsTo(TpClass::class);
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function divisions(){
        return $this->hasMany(ClassDivision::class);
    }
}
