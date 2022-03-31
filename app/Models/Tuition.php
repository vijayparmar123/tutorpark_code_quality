<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Tuition extends Eloquent
{
    protected $fillable = [
        'subject_id', 'syllabus_id', 'class_id', 'title', 'description', 'tutor_id', 'start_date', 'end_date', 'start_time', 'end_time', 'student_ids', 'attended_student_id', 'mode_of_teaching', 'schedule_id', 'cost', 'enable_students', 'disable_students', 'image' , 'demo_video','library_id'
    ];
    
    protected $dates = [
        'start_date', 'end_date'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(TpClass::class,'class_id');
    }
    
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class,'tutor_id');
    }
    
    public function availability()
    {
        return $this->belongsToMany(TutorTimeTable::class,'schedule_id');
    }
    
    public function sessions()
    {
        return $this->hasMany(Sessions::class,'tuition_id');
    }
    
    public function students()
    {
        return $this->belongsToMany(UserDetails::class,NULL,'subscribed_tuition_ids','student_ids');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class,'tutor_id');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }
	
	public function library()
    {
        return $this->belongsTo(Library::class);
    }

    public function payment()
    {
        return $this->morphMany(RazorpayPaymentDetail::class, 'payable');
    }
}
