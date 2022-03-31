<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentBasicDetailResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'syllabus' => $this->syllabus->name,
            'class' => ($this->class)?$this->class->name:null,
            'subject' => $this->subject->name,
            'from_date' => getDateF($this->from_date),
            'to_date' => getDateF($this->to_date),
            'total_mark' => $this->total_mark,
            'image' => $this->image ?  url('storage/' . $this->image) : null,
            'total_attempted' =>  ($this->studentAssignment()) ? $this->studentAssignment()->where(['student_status'=>'attempted'])->count(): null,
        ];
    }
}
