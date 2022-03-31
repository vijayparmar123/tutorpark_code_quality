<?php

namespace App\Models;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Scopes\MySchoolData;
// use Illuminate\Database\Eloquent\Model;

class Library extends Eloquent
{
    protected $fillable = [
        'name', 'syllabus_id','class_id', 'subject_id', 'topic', 'description','attachments','visit_count','created_by','created_date'
    ];

    protected static function booted()
    {
        static::creating(function ($library) {
                $library->created_by = auth()->user()->id;
        });

        static::addGlobalScope(new MySchoolData);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
      }
  
      public function syllabus(){
        return $this->belongsTo(Syllabus::class);
      }
      public function topic(){
        return $this->belongsTo(Topic::class);
      }
  
      public function class(){
        return $this->belongsTo(TpClass::class);
      }
      public function course(){
        return $this->hasOne(Course::class );
      }

      public function creator()
      {
          return $this->belongsTo(User::class,'created_by');
      }

      public function comments()
      {
        return $this->morphMany(Comment::class, 'commentable');
      }
	  
	    public function linkTimeline()
      {
        return $this->morphMany(Timeline::class, 'linkable');
      }
    
}
