<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscribeCourseResource extends JsonResource
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
            'course_id'=>$this->course,
            'student_id'=>$this->user,
            'end_date'=>$this->end_date,
            'completed_status'=>$this->completed_status,
            'progress'=>$this->progress
        ];
    }
}
