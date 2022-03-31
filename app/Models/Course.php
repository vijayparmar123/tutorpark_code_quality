<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
//use Subject;
class Course extends Eloquent
{
  protected $fillable = [
    'title', 'description', 'subject_id', 'syllabus_id', 'class_id', 'library_id', 'total_ratings', 'avg_ratings', 'cost', 'tp_points', 'mode_of_teaching', 'type', 'course_type', 'course_video', 'demo_video', 'logo', 'number_of_sessions', 'duration_for_complete', 'topic', 'library_ids', 'number_of_people_attending_course', 'assignments_conducted', 'schedule_with_time', 'start_date', 'end_date', 'start_time', 'end_time', 'number_of_videos', 'number_of_assignments', 'paid_amount', 'payment_accepted', 'payment_date', 'number_of_people_attending', 'course_topics', 'created_by'
  ];

  protected $dates = [
    "start_date",
    "end_date",
    "payment_date",
  ];

  protected static function booted()
  {
    static::creating(function($course){
      $course->created_by = auth()->id();
    });
  }

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
    return $this->belongsTo(TpClass::class);
  }
  
  public function library()
  {
    return $this->belongsTo(Library::class);
  }
  
  public function topic()
  {
    return $this->belongsTo(Topic::class);
  }

  // public function library()
  // {
    // return $this->belongsTo(Library::class, 'library_ids');
  // }

  public function author()
  {
    return $this->belongsTo(User::class,'created_by');
  }

  // public function subscribeCourse(){
  public function subscriptions()
  {
    return $this->hasMany(CourseSubscription::class);
  }

  public function messages()
  {
    return $this->hasMany(CourseMessage::class);
  }

  public function isCity($city)
  {
    return strtolower($this->author->details->city) == strtolower($city);
  }

  public function isGender($gender)
  {
    return strtolower($this->author->details->gender) == strtolower($gender);
  }

  public function payment()
  {
      return $this->hasManyThrough(RazorpayPaymentDetail::class, CourseSubscription::class, 'course_id','payable_id')->where(
        'payable_type', 'App\Models\CourseSubscription');
  }
}
