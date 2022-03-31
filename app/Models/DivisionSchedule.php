<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DivisionSchedule extends Eloquent
{
    protected $fillable = [
        'division_id', 'teacher_id', 'subject_id', 'day', 'start_time', 'end_time','status', 'created_by'
    ];

    protected static function booted()
    {
        static::creating(function ($schedule) {
                $schedule->status = 1;
                $schedule->created_by = auth()->user()->_id;
        });

        static::addGlobalScope('activeSchedule', function (Builder $builder) {
            $builder->where('status', '!=', 0);
        });
    }
    
    public function division(){
        return $this->belongsTo(ClassDivision::class, 'class_division_id');
    }

    public function subject(){
        return $this->belongsTo(Subject::class,'subject_id');
    }

    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }

    public function class(){
        return $this->belongsTo(SchoolClass::class,'school_class_id');
    }

    public function sessions(){
        return $this->hasMany(DivisionSession::class);
    }
}
