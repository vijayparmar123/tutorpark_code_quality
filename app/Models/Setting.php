<?php

namespace App\Models;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Setting extends Eloquent
{
    protected $fillable = [
        'student_point', 'tutor_point', 'mode', 'test_key_id', 'test_secret', 'live_key_id', 'live_secret', 'created_by', 'updated_by'
    ];

    protected static function booted()
    {
        static::creating(function ($setting) {
            $setting->created_by = auth()->user()->id;
        });

        static::updated(function ($setting) {
            $setting->updated_by = auth()->user()->id;
        });
    }
}
