<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DivisionSubjectTeacherResource extends JsonResource
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
            'teacher_id' => ($this->teacher)?$this->teacher->_id:null,
            'teacher_name' => ($this->teacher)?$this->teacher->full_name:null,
            'tp_id' => ($this->teacher)?$this->teacher->details->tp_id:null,
            'subject_id' => ($this->subject)?$this->subject->_id:null,
            'subject_name' => ($this->subject)?$this->subject->name:null,
            'is_class_teacher' => ($this->is_class_teacher)?$this->is_class_teacher:false, 
            'is_ass_class_teacher' => ($this->is_ass_class_teacher)?$this->is_ass_class_teacher:false,
            'status' => $this->status
        ];
    }
}
