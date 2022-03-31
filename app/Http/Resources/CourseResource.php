<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $urlPrefix = function ($image) {
            return $image ? urldecode(url('storage', $image)) : null;
        };

        return [
            'id' => $this->id,
            'title' => $this->title,
            'instructor' => ($this->author)?$this->author->full_name:null,
            'description' => $this->description,
            'syllabus_name' => ($this->syllabus)?$this->syllabus->name:null,
            'subject_name' => ($this->subject)?$this->subject->name:null,
            'class_name' => ($this->class)?$this->class->name:null,
            'total_ratings' => $this->total_ratings,
            'avg_ratings' => $this->avg_ratings,
            'cost' => $this->cost,
            // 'tp_points' => $this->tp_points,
            'mode_of_teaching' => $this->mode_of_teaching,
            'type' => $this->type,
            'demo_video' => $this->demo_video ? url('storage/' . $this->demo_video) : null,
            'logo' => $this->logo ?  url('storage/' . $this->logo) : null,
            'number_of_sessions' => $this->number_of_sessions,
            'duration_for_complete' => $this->duration_for_complete,
            'topic_id' => $this->topic,
            // 'number_of_people_attending_course' => $this->number_of_people_attending_course,
            'assignments_conducted' => $this->assignments_conducted,
            'schedule_with_time' => $this->schedule_with_time,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            // 'number_of_videos' => $this->number_of_videos,
            // 'number_of_assignments' => $this->number_of_assignments,
            // 'paid_amount' => $this->paid_amount,
            // 'payment_accepted' => $this->payment_accepted,
            // 'payment_date' => $this->payment_date,
            'total_videos' => $this->number_of_videos,
            'total_assignments' => $this->number_of_assignments,
			'number_of_people_attending' => $this->number_of_people_attending, 
            'course_topics' => $this->course_topics,
            'library' => ($this->library)?new LibraryResource($this->library):null,
        ];
    }
}
