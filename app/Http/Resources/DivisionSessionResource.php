<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DivisionSessionResource extends JsonResource
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
            'division_id' => ($this->division)?$this->division->_id:null,
            'division' => ($this->division)?$this->division->name:null,
            'class' => ($this->division)?(($this->division->class)?new SchoolClassResource($this->division->class):null):null,
            'teacher_id' => ($this->teacher)?$this->teacher->id:null,
            'teacher_name' => ($this->teacher)?$this->teacher->full_name:null,
            'subject_id' => ($this->subject)?$this->subject->_id:null,
            'subject_name' => ($this->subject)?$this->subject->name:null,
            'total_students' => ($this->division)?$this->division->students()->count():0,
            'date' => date('d-m-Y',strtotime($this->date)),
            'day' => ucfirst($this->day),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'meeting_id' => $this->meeting_id,
            'total_present' => ($this->attendance)?$this->attendance()->where(['status'=>'present'])->count():null,
            'total_absent' => ($this->attendance)?$this->attendance()->where(['status'=>'absent'])->count():null,
            'attendance' => ($this->attendance)?DivisionAttendanceResource::collection($this->attendance):null,
        ];
    }
}
