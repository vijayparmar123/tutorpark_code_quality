<?php

namespace App\Models;
//use Course;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
//use SebastianBergmann\Environment\Console;

class Subject extends Eloquent
{
  protected $fillable = [
    'name', 'class_id', 'active', 'description'
  ];

  public function course()
  {
    return $this->hasOne(Course::class);
  }

  public function topic()
  {
    return $this->hasOne(Topic::class);
  }

  public function library()
  {
    return $this->hasOne(Library::class);
  }

  public function classes()
  {
    // return $this->belongsTo(Classes::class);
    return $this->belongsToMany(TpClass::class,null,'subject_ids','class_ids');
  }

  public function userDetails()
  {
    return $this->belongsTo(UserDetails::class);
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
    return $this->belongsToMany(UserDetails::class, null,'preferred_subjects','tutors_can_teach');
  }
}
