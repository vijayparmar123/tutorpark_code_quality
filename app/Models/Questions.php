<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Scopes\MySchoolData;

class Questions extends Eloquent
{
    protected $fillable = [
        'subject_id', 'syllabus_id', 'class_id', 'division_id','topic_name','created_by', 'question','like','dislike', 'library_id'
    ];
    
    protected static function booted()
    {
        static::addGlobalScope(new MySchoolData);
    }

    public function answers()
    {
        return $this->hasMany(Answers::class,'question_id');
    }

    public function subject()
    {
    return $this->belongsTo(Subject::class);
    }

    public function syllabus()
    {
    return $this->belongsTo(Syllabus::class);
    }

    public function class()
    {
    return $this->belongsTo(TpClass::class,'class_id');
    }

    public function division()
    {
    return $this->belongsTo(ClassDivision::class,'division_id');
    }

    public function topic()
    {
    return $this->belongsTo(Topic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }
	
	public function getMyStudentQuestionAttribute($value)
	{
		$myStudents = myStudents();
		if(in_array($this->created_by, $myStudents))
		{
			return true;
		}else{
			return false;
		}
	}

}
