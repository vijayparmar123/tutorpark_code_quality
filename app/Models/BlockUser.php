<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class BlockUser extends Model
{
    protected $fillable = [
        "user_id",
        "blocked_user",
        "unblock_time",
        "is_spam",
        "message",
    ];

    protected $dates = [
        "unblock_time",
    ];

    protected static function booted()
    {
        static::creating(function($block){
            $block->unblock_time = \Carbon\Carbon::now()->add(6,'month');
        });
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class);
    }
}
