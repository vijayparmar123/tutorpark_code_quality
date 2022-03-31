<?php

namespace App\Traits;

use App\Models\DivisionStudent;
use App\Models\DivisionSubjectTeacher;
use App\Jobs\AssignSchoolFriends;

trait ManageSchool
{
    /**
     * Get true or false that user associated school
     * 
     * @return collection
     */
    public function hasSchool()
    {
        return ($this->school_id)?true:false;
    }

    /**
     * Get School ID
     * 
     * @return void
     */
    public function getSchoolID()
    {
        return $this->school_id;
    }

    /**
     * Associate School to user
     * 
     * @return void
     */
    public function assignSchool($schoolID)
    {

        $assign = $this->school()->associate($schoolID)->save();
        dispatch(new AssignSchoolFriends($this->_id));
        return $assign;
    }

    /**
     * Get school user ids
     * 
     * @return void
     */
    public function getSchoolUsers($schoolID)
    {
        return User::where('school_id',$schoolID)->pluck('_id')->toArray();
    }
	
	/**
     * Get user's division id
     * 
     * @return void
     */
    public function getDivisionIds()
    {
        if($this->hasRole('school-student'))
        {
            return DivisionStudent::where(['student_id'=>$this->_id])->pluck('class_division_id')->toArray();
        }elseif($this->hasRole('school-tutor'))
        {
            return DivisionSubjectTeacher::where(['teacher_id'=>$this->_id])->pluck('class_division_id')->toArray();
        }else{
            return array();
        }
        
    }

    /**
     * Get user's division id
     * 
     * @return void
     */
    public function isDisabledInDivision($divisionId)
    {
        if($this->hasRole('school-student'))
        {
            return DivisionStudent::where(['class_division_id'=>$divisionId,'student_id'=>$this->_id,'status'=>0])->count();
        }elseif($this->hasRole('school-tutor'))
        {
            return DivisionSubjectTeacher::where(['class_division_id'=>$divisionId,'teacher_id'=>$this->_id,'status'=>0])->pluck('class_division_id')->toArray();
        }else{
            return 0;
        }
        
    }

    /**
     * Check student is subject leader or not
     * 
     * @return void
     */
    public function isSubjectLeader()
    {
        return DivisionSubjectTeacher::where(['class_division_id'=>$divisionId,'teacher_id'=>$this->_id,'status'=>0])->pluck('class_division_id')->toArray();        
    }
    
}
