<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $subscription = $this->subscriptions()->where('user_id', auth()->id());

        $isSubscribed = $subscription->exists();
        $status = $subscription->exists() ? $subscription->first()->status : null;
        $endDate = $subscription->exists() && $subscription->first()->end_date ? $subscription->first()->end_date : false;

        $payment = null;
        if(auth()->user()->hasRole('student'))
        {
            $payment = $this->subscriptions()->where(['user_id' => auth()->user()->_id])->first()->payment()->first();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'instructor' => $this->author->full_name,
            'tp_points_balance' => ($this->author->points)?$this->author->points->balance:0,
            'description' => $this->description,
            'phone' => $this->author->details->phone,
            'syllabus_name' => $this->syllabus->name,
            'subject_name' => $this->subject->name,
            'class_name' => ($this->class)?$this->class->name:null,
            'syllabus_id' => $this->syllabus_id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'total_ratings' => $this->total_ratings,
            'avg_ratings' => $this->avg_ratings,
            'cost' => $this->cost,
            'mode_of_teaching' => $this->mode_of_teaching,
            'type' => $this->type,
            'demo_video' => $this->demo_video ? url('storage/' . $this->demo_video) : null,
            'logo' => $this->logo ?  url('storage/' . $this->logo) : null,
            'course_video' => $this->course_video ?  url('storage/' . $this->course_video) : null,
            'course_type' => $this->course_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => date("h:i A",strtotime($this->start_time)),
            'end_time' => date("h:i A",strtotime($this->end_time)),
            'start_time_original' => $this->start_time,
            'end_time_original' => $this->end_time,
            'avg_rating' => rand(0,5),
            'total_videos' => $this->number_of_videos,
            'total_assignments' => $this->number_of_assignments,
            'is_my' => $this->created_by == auth()->id() ? true : false,
            'is_subscribed' => $isSubscribed,
            'status' => $status,
            'progress_percent' => rand(0, 100),
            'is_completed' => ($status == 'compeleted')?true:false, 
            'completed_at' => $endDate, 
            'number_of_people_attending' => $this->number_of_people_attending, 
            'course_topics' => $this->course_topics, 
			'library' => ($this->library)?new LibraryResource($this->library):null,
            // 'payment'=> ($this->payment)?RazorpayPaymentResource::collection($this->payment):null,
            'payment'=> ($payment)?new RazorpayPaymentResource($payment):null,
        ];
    }
}
