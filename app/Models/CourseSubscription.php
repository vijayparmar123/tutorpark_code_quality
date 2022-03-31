<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class CourseSubscription extends Eloquent
{
	protected $fillable = [
		'course_id', 'user_id', 'start_date', 'end_date', 'status',
	];

	protected $dates = [
		'start_date',
		'end_date'
	];

	public function course()
	{
		return $this->belongsTo(Course::class);
	}

	public function subscribedUser()
	{
		return $this->belongsTo(User::class);
	}
  
	public function transactions()
	{
		return $this->morphMany(Transaction::class, 'transactable');
	}
	
	public function payment()
    {
        return $this->morphMany(RazorpayPaymentDetail::class, 'payable');
    }
}
