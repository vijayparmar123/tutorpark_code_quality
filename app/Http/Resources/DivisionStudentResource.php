<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DivisionStudentResource extends JsonResource
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
            'id'=> ($this->student)?$this->student->id:null,
            'name' => ($this->student)?$this->student->full_name:null,
            'email' => ($this->student)?$this->student->email:null,
            'tp_id' => ($this->student)?($this->student->details)?$this->student->details->tp_id:null:null,
            'profile' => ($this->student)?($this->student->details) ? url('storage/images/user/'.strtolower($this->student->details->gender).".jpg") : null:null,
            'status' => ($this->student)?$this->status:null, 
            'is_class_leader' => ($this->is_leader)?$this->is_leader:false, 
            'is_subject_leader' => ($this->subjectLeadership->count())?true:false, 
            'subjectLeadership' => ($this->subjectLeadership->count())?LeaderSubjectstResource::collection($this->subjectLeadership):null,
        ];
    }
}
