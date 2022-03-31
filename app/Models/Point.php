<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Point extends Eloquent
{
    protected $fillable = [
        'user_id','balance'
    ];

    public function history()
    {
        return $this->hasMany(PointHistory::class,'point_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
