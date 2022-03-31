<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Feedback extends Eloquent
{
    protected $fillable = [
        'title', 'detailed_feedback','feedback_for','feedback_reference_id', 'given_by', 'date', 'total_ratings','avg_ratings','feedback_for_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'given_by');
    }
}
