<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TpClass extends Eloquent
{
  protected $collection = 'classes';

  protected $fillable = [
    'name', 'syllabus_id', 'level_id', 'school_id','description', 'status', 'created_by'
  ];

  public function syllabus()
  {
    return $this->belongsTo(Syllabus::class);
  }

  public function course()
  {
    return $this->hasMany(Course::class);
  }

  public function level()
  {
    return $this->belongsTo(Level::class);
  }

  public function subjects()
  {
    // return $this->hasOne(Subject::class);
    return $this->belongsToMany(Subject::class, null, 'class_ids', 'subject_ids');
  }

  public function texBook()
  {
    return $this->belongsTo(TextBook::class);
  }

  public function jobs()
  {
    return $this->hasMany(TpJob::class);
  }

  public function tutorsCanTeach()
  {
    return $this->belongsToMany(UserDetails::class, null,'preferred_classes','tutors_can_teach');
  }
  public function user()
  {
      return $this->belongsTo(User::class,'created_by');
  }
}
