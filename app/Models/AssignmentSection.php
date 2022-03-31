<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class AssignmentSection extends Eloquent
{
    protected static function booted()
    {
        // Delete all question of specific section when section deleted
        static::deleting(function($section) { 
            $section->questions()->each(function($question) {
               $question->delete(); 
            });
       });
    }

    protected $fillable = [
        'assignment_id', 'section_id', 'section_type', 'description', 'total_marks'
    ];

    public function assignment(){
        return $this->belongsTo(assignment::class,'assignment_id');
    }

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function type(){
        return $this->belongsTo(QuestionType::class,'section_type');
    }

    public function questions(){
        return $this->hasMany(SectionQuestion::class);
    }
}
