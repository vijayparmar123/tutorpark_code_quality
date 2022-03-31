<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TutorScheduleResource extends JsonResource
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
            'id'=>$this->id,
            // 'teacher_id' => $this->teacher->_id,
            // 'teacher_name' => $this->teacher->full_name,
            // 'teacher_tpid' => $this->teacher->details->tp_id,
            'class' => ($this->division)?$this->division->class->class_name:'',
            'division' => ($this->division)?$this->division->name:'',
            'subject_id' => ($this->subject)?$this->subject->_id:'',
            'subject_name' => ($this->subject)?$this->subject->name:'',
            'day' => ucfirst($this->day),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }
}
