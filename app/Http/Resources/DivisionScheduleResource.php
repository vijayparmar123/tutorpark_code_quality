<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DivisionScheduleResource extends JsonResource
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
            'teacher_id' => ($this->teacher)?$this->teacher->_id:null,
            'teacher_name' => ($this->teacher)?$this->teacher->full_name:null,
            'teacher_tpid' => ($this->teacher)?$this->teacher->details->tp_id:null,
            'subject_id' => ($this->subject)?$this->subject->_id:null,
            'subject_name' => ($this->subject)?$this->subject->name:null,
            'day' => ucfirst($this->day),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }
}
