<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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
            "posted_by" => $this->author->full_name,
            "posted_by_email" => $this->author->email,
            'image' => url('storage/images/user/'.strtolower($this->author->details->gender).".jpg"),
            "class" => ($this->class)?$this->class->name:null,
            "syllabus" => $this->syllabus->name,
            "school" => $this->school ? $this->school->name : null,
            "requirements" => $this->requirements,
            "topic" => $this->topic,
            "type" => $this->type,
            "area" => $this->author->city,
            "start_time" => $this->start_time != null ? date("h:i A",strtotime($this->start_time)) : null,
            "end_time" => $this->end_time != null ? date("h:i A",strtotime($this->end_time)) : null,
        ];
    }
}
