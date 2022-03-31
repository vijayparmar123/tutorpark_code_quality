<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscribedCourseListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // dd($this->course->title);
        $subscription = $this->course->subscriptions()->where('user_id', auth()->id());
        $status = $subscription->exists() ? $subscription->first()->status : null;
        $endDate = $subscription->exists() && $subscription->first()->end_date ? $subscription->first()->end_date : false;


        return [
            'id' => ($this->course)?$this->course->id:null,
            'title' => ($this->course)?$this->course->title:null,
            'description' => ($this->course)?$this->course->description:null,
            'syllabus_name' => ($this->course)?(($this->course->syllabus)? $this->course->syllabus->name : null):null,
            'subject_name' => ($this->course)?(( $this->course->subject) ? $this->course->subject->name : null):null,
            'class_name' => ($this->course)?(($this->course->class)?$this->course->class->name:null):null,
            'logo' => ($this->course)?($this->course->logo ?  url('storage/' . $this->course->logo) : null):null,
            'status' => $status,
            'progress_percent' => rand(0, 100),
            'is_completed' => ($status == 'compeleted')?true:false, 
            'completed_at' => $endDate, 
        ];
    }
}
