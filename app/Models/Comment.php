<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Comment extends Eloquent
{
    protected $fillable = [
        'body','datetime','commented_by',
    ];

    protected $dates = [
        'datetime'
    ];

    public function commenter()
    {
        return $this->belongsTo(User::class,'commented_by');
    } 

    public function commentable()
    {
        return $this->morphTo();
    }
}
