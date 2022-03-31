<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'paid_to',
        'paid_from',
        'date',
        'payment_mode',
        'transaction_id',
        'amount',
        'tp_commission',
        'final_amount',
        'payment_status',
        'model',
        'model_id',
        'mode_of_teaching',
        'purpose',
    ];

    protected $dates = [
        'date'
    ];

    /**
     * Get the parent transactable model (tuition or event).
     */
    public function transactable()
    {
        return $this->morphTo();
    }

    public function to_user()
    {
        return $this->belongsTo(User::class,'paid_to');
    }

    public function from_user()
    {
        return $this->belongsTo(User::class,'paid_from');
    }
}
