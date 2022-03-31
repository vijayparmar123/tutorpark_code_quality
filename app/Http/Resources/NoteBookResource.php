<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NoteBookResource extends JsonResource
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
            'subject' => $this->subject ? $this->subject->name : null,
            'subject_id' => $this->subject ? $this->subject->_id : null,
            'syllabus' => $this->syllabus ? $this->syllabus->name : null,
            'syllabus_id' => $this->syllabus ? $this->syllabus->_id : null,
            'class' => $this->class ? $this->class->name : null,
            'class_id' => $this->class ? $this->class->_id : null,
            'notebook_name' => $this->notebook_name,
            'tutor' => $this->tutor ? $this->tutor->first_name." ".$this->tutor->last_name  : null,
            'tutor_id' => $this->tutor_id ? $this->tutor_id : null,
            'description' => $this->description,
            'created_at' => getDateTime($this->created_at),
            'image' => $this->image ? url('storage/' . $this->image) : null,
        ];
    }
}
