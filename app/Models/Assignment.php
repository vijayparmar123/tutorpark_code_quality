<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Assignment extends Eloquent
{
    protected $fillable = [
        'syllabus_id', 'class_id', 'subject_id', 'title', 'description', 'total_mark','image', 'from_date', 'to_date', 'release_date', 'is_released', 'is_expired', 'created_by'
    ];

    protected $dates = [
        'from_date', 'to_date', 'release_date'
    ];

    protected static function booted()
    {
        static::creating(function ($assignment) {
            $assignment->from_date = date('Y-m-d H:i:s');
            $assignment->to_date = date('Y-m-d H:i:s');
            $assignment->is_released = false;
            $assignment->is_expired = false;
            $assignment->created_by = auth()->user()->id;
        });

        // Delete all section of specific assignment when assignment deleted
        static::deleting(function($assignment) { 
            $assignment->sections()->each(function($section) {
               $section->delete(); 
            });
       });
    }

    public function author(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function syllabus(){
        return $this->belongsTo(Syllabus::class);
    }

    public function class(){
        return $this->belongsTo(TpClass::class);
    }

    public function sections(){
        return $this->hasMany(AssignmentSection::class,'assignment_id');
    }

    public function studentAssignment(){
        return $this->hasMany(StudentAssignment::class,'assignment_id');
    }
}
