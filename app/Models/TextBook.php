<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TextBook extends Eloquent
{
  protected $fillable = [
    'subject_id', 'syllabus_id', 'class_id', 'book_name', 'description', 'external_link', 'resource_type', 'attachment', 'image'
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
}
