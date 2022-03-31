<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TutorTimeTable;

class TutionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $schedule = array();
        if(!empty($this->schedule_id)){
            $schedule = TutorTimeTable::whereIn('_id',$this->schedule_id)->get();
            
        }
        
        return [
            'id' => $this->id,
            'subject_id' => $this->subject ? $this->subject->_id : null,
            'subject' => $this->subject ? $this->subject->name : null,
            'syllabus_id' => $this->syllabus ? $this->syllabus->_id : null,
            'syllabus' => $this->syllabus ? $this->syllabus->name : null,
            'class_id' => $this->class ? $this->class->_id : null,
            'class' => $this->class ? $this->class->name : null,
            'title' => $this->title ? $this->title : null,
            'description' => $this->description ? $this->description : null,
            'tutor_name' => $this->user ? $this->user->full_name : null,
            'tutor_email' => $this->user ? $this->user->email : null,
            'tutor_details' => new UserDetailsResource($this->user->details),
            'image' => ($this->image) ?  url('storage/' . "{$this->image}") : null,
            'demo_video' => ($this->demo_video) ?  url('storage/' . "{$this->demo_video}") : "",
            'mode_of_teaching' => str_replace('_',' ',ucwords($this->mode_of_teaching,'_')),
            'start_date' => date("d-m-Y", strtotime($this->start_date)),
            'end_date' => date("d-m-Y", strtotime($this->end_date)),
            'cost' => $this->cost ? $this->cost : 0,
            'student_count' => ($this->students)?$this->students()->count():0,
            'availability'=> TuitionScheduleResource::collection($schedule),
			'library' => ($this->library)?new LibraryResource($this->library):null,
            'payment'=> ($this->payment)?RazorpayPaymentResource::collection($this->payment):null,
        ];
    }
}
