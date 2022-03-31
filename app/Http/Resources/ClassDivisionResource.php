<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClassDivisionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->_id,
            'is_disabled' => (auth()->user()->isDisabledInDivision($this->_id))?true:false,
            'name' => ($this->division)?$this->division->name:null,
            'standard' => ($this->class)?(($this->class->class)?$this->class->class->name:null):null,
            'class' => ($this->class)?new SchoolClassResource($this->class):null,
            'have_class_teacher' => ($this->classTeacher)?$this->classTeacher()->count():0,
            'have_ass_class_teacher' => ($this->aasClassTeacher)?$this->aasClassTeacher()->count():0,
            'total_subject_teacher' =>  ($this->SubjectTeacher)?$this->SubjectTeacher->count():0,
            'subject_teacher' => ($this->SubjectTeacher)?DivisionSubjectTeacherResource::collection($this->SubjectTeacher):null, 
            'students' => ($this->students)?DivisionStudentResource::collection($this->students):null, 
            'subjectLeaders' => ($this->subjectLeaders)?DivisionSubjectLeadersResource::collection($this->subjectLeaders):null, 
            'schedule' => ($this->schedule)?DivisionScheduleResource::collection($this->schedule):null,
            'total_student' =>  ($this->students)?$this->students->count():0,
            'total_present' =>  ($this->todayAttendance)?count($this->todayAttendance()->where(['status'=>'present'])->get()):null,
            'total_absent' =>  ($this->todayAttendance)?count($this->todayAttendance()->where(['status'=>'absent'])->get()):null,
            'attendance' =>  ($this->todayAttendance)?DivisionAttendanceResource::collection($this->todayAttendance):null,
        ];
    }
}
