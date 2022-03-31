<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DivisionSession extends Eloquent
{
    protected $fillable = [
        'class_division_id', 'date', 'teacher_id', 'subject_id', 'day', 'start_time', 'end_time', 'meeting_id','status'
    ];

    protected static function booted()
    {
        static::creating(function ($schedule) {
                $schedule->status = 1;
        });

        static::addGlobalScope('activeSession', function (Builder $builder) {
            $builder->where('status', '!=', 0);
        });
    }

    public function division(){
        return $this->belongsTo(ClassDivision::class,'class_division_id');
    }

    public function schedule(){
        return $this->belongsTo(DivisionSchedule::class,'division_schedule_id');
    }

    public function subject(){
        return $this->belongsTo(Subject::class,'subject_id');
    }

    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }

    public function attendance(){
        return $this->hasMany(DivisionAttendance::class);
    }
}
