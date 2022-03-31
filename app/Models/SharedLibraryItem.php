<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SharedLibraryItem extends Eloquent
{
    protected $fillable = [
        'library_id','share_to','share_by','created_by'
    ];
	
	public function library()
    {
        return $this->belongsTo(Library::class,'library_id');
    }
	
	public function shareBy()
    {
        return $this->belongsTo(User::class,'share_by');
    }
	
	public function shareTo()
    {
        return $this->belongsTo(User::class,'share_to');
    }
}
