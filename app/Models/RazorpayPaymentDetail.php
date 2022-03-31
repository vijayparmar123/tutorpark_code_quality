<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class RazorpayPaymentDetail extends Eloquent
{
    protected $fillable = [
        'user_id','razorpay_order_id','razorpay_payment_id','razorpay_signature','created_by',
    ];

    // protected static function booted()
    // {
    //     if(auth()->user()->hasRole('student') || auth()->user()->hasRole('school-student'))
    //     {
    //         static::addGlobalScope('mypayment', function (Builder $builder) {
    //             $builder->where(['created_by' => auth()->user()->_id]);
    //         });
    //     }
    // }

    public function payable()
    {
        return $this->morphTo();
    }

    public function paymentBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}
