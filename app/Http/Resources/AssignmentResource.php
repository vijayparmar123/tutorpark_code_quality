<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $student_assignment_id = null;
        $student_assignment_id = ($this->studentAssignment()->where(['student_id' => auth()->user()->id])->first())?$this->studentAssignment()->where(['student_id' => auth()->user()->id])->first()->_id:null;
        $tutor_status = ($this->studentAssignment()->where(['student_id' => auth()->user()->id])->first())?$this->studentAssignment()->where(['student_id' => auth()->user()->id])->first()->tutor_status:null;
        $student_obtained_mark = ($this->studentAssignment()->where(['student_id' => auth()->user()->id])->first())?$this->studentAssignment()->where(['student_id' => auth()->user()->id])->first()->obtained_mark:null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'syllabus' => ($this->syllabus)?$this->syllabus->name:null,
            'class' => ($this->class)?$this->class->name:null,
            'subject' => ($this->subject)?$this->subject->name:null,
            'from_date' => getDateF($this->from_date),
            'to_date' => getDateF($this->to_date),
            'total_mark' => $this->total_mark,
            'image' => $this->image ?  url('storage/' . $this->image) : null,
            'total_attempted' =>  ($this->studentAssignment()) ? $this->studentAssignment()->where(['student_status'=>'attempted'])->count(): null,
            'student_assignment_id'=> $student_assignment_id,
            'tutor_status'=> $tutor_status,
            'obtained_mark'=> $student_obtained_mark,
            'sections' => ($this->sections)?AssignmentSectionResource::collection($this->sections):null,
        ];
    }
}
