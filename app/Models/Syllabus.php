<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Syllabus extends Eloquent
{
  
  protected $collection = 'syllabuses';

  protected $fillable = [
    'name', 'active', 'description'
  ];

  public function course()
  {
    return $this->hasOne(Course::class);
  }
  
  public function library()
  {
    return $this->hasOne(Library::class);
  }

  public function classes()
  {
    return $this->hasMany(TpClass::class);
  }

  public function userDetails()
  {
    return $this->hasOne(UserDetails::class);
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
    return $this->belongsToMany(UserDetails::class, null,'preferred_boards','tutors_can_teach');
  }
}
