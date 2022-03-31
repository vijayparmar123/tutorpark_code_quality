<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class PointHistory extends Eloquent
{
    protected $fillable = [
        'datetime','comment','transaction_type','points','source_of_point','transferred_from','transferred_to','payment_mode','amount'
    ];

    protected $dates = [
        'datetime'
    ];

    // public function transferHistory()
    // {
    //     return $this->belongsTo(PointTransferHistory::class,null,'history_ids','transfer_history_id');
    // }

    public function transferBy()
    {
        return $this->belongsTo(User::class,'transferred_from');
    }

    public function transferTo()
    {
        return $this->belongsTo(User::class,'transferred_to');
    }

    public function payment()
    {
        return $this->morphMany(RazorpayPaymentDetail::class, 'payable');
    }
}
