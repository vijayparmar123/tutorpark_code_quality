<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

class DivisionAttendance extends Eloquent
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        if(auth()->user()->hasRole('school-student'))
        {
            static::addGlobalScope('myattendance', function (Builder $builder) {
                $builder->where('student_id', '=', auth()->user()->_id);
            });
        }
    }

    protected $fillable = [
        'class_division_id', 'student_id', 'date', 'status', 'created_by'
    ];

    public function student(){
        return $this->belongsTo(User::class,'student_id');
    }
}
