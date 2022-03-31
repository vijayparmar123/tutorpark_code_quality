<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Answers extends Eloquent
{
    protected $fillable = [
        'question_id', 'answer','created_by','library_id'
      ];
    
    public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }

	public function library()
    {
        return $this->belongsTo(Library::class);
    }
}
