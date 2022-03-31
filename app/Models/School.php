<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class School extends Eloquent
{
    protected $fillable = [
        'type','school_name', 'registration_no', 'pincode', 'city', 'phone', 'email', 'mobile', 'principal', 'vice_principal', 'incharge', 'working_start_date', 'working_end_date', "attachment", "image", "is_verified", "verified_by", "created_by",
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function invoice()
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }
}
