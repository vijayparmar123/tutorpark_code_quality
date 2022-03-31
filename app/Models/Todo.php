<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Todo extends Eloquent
{
    protected $fillable = [
        'name', 'user_id', 'is_completed', 'mark_date'
    ];

    protected $dates = [
        'mark_date'
      ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
