<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class RejectedFriendRequest extends Eloquent
{
    protected $fillable = ['rejected_date', 'rejected_to', 'rejected_by','created_by'];

    public function rejectedBy()
    {
        return $this->belongsTo(User::class,'rejected_by');
    }

    public function rejectedTo()
    {
        return $this->belongsTo(User::class,'rejected_to');
    }
}
