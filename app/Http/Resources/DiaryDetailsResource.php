<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiaryDetailsResource extends JsonResource
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
            'subject_id' => ($this->subject)?$this->subject->id:null,
            'subject_name' => ($this->subject)?$this->subject->name:null,
            'class_work' => ($this->class_work)?$this->class_work:null,
            'class_work_attachment' => ($this->class_work_attachment) ?  url('storage/' . $this->class_work_attachment):null,
            'home_work' => ($this->home_work)?$this->home_work:null,
            'home_work_attachment' => ($this->home_work_attachment) ?  url('storage/' . $this->home_work_attachment):null,
            'tomorrow_topics' => ($this->tomorrow_topics)?$this->tomorrow_topics:null,
            'added_by' => $this->diary->createdBy->full_name,
            'user_type' => ($this->diary->createdBy->hasRole('school-tutor'))?'teacher':'leader',
        ];
    }
}
