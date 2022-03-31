<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SessionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
		$attendance_status = ($this->attendance()->where(['student_id'=>auth()->user()->_id])->first())?$this->attendance()->where(['student_id'=>auth()->user()->_id])->first()->status:null;
        return [
            "session_id" =>  $this->id,
            "tuition_image" =>  url('storage/' .$this->tuition->image),
            "tuition_id" => $this->tuition->_id,
            "tuition_title" => $this->tuition->title,
            "tutor_name" => $this->tuition->tutor->full_name,
            "schedule_date" =>  date("d-m-Y",strtotime($this->date)),
            "from_time" =>  date("h:i A",strtotime($this->start_time)),
            "end_time" =>  date("h:i A",strtotime($this->end_time)),
            "total_students" => $this->tuition->students()->count(),
            "total_videos" => 10,
            "total_assignments" => 11,
            "avg_rating" => rand(0, 4),
            "meeting_id" => $this->meeting_id,
            "is_completed" => $this->is_completed,
            "attendance_status" => $attendance_status
        ];
    }
}
