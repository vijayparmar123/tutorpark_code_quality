<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class NoteBook extends Eloquent
{
    protected $fillable = [
        'subject_id', 'syllabus_id', 'class_id','user_id', 'notebook_name', 'description', 'image','tutor_id'
      ];
    
      public function subject()
      {
        return $this->belongsTo(Subject::class);
      }
    
      public function syllabus()
      {
        return $this->belongsTo(Syllabus::class);
      }
    
      public function class()
      {
        return $this->belongsTo(TpClass::class,'class_id');
      }

      public function user()
      {
          return $this->belongsTo(User::class);
      }
      
      public function tutor()
      {
        return $this->belongsTo(User::class, 'tutor_id');
      }
}
