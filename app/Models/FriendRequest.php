<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class FriendRequest extends Model
{
    
    protected $fillable = [
        "from",
        "to",
        "message",
        "is_invited"
    ];

    public function sender()
    {
        return $this->belongsTo(User::class,'to','email');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class,'from','email');
    }
}
