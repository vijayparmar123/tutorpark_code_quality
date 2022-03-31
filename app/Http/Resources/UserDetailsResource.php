<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
		$tutor_rating = null;
		if($this->user->hasRole('tutor'))
		{
			$tutor_rating = getTutorAVGRating($this->user->id);
		}
        // dump($this->preferredClasses->isNotEmpty());
        return [
			'profile' => url('storage/images/user/'.strtolower($this->gender).".jpg"),
            'gender' => $this->gender,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null,
            'country' => $this->country,
            'nationality' => $this->nationality,
            'aadhar_id' => $this->aadhar_id,
            'area' => $this->area,
            'address' => $this->address,
            'pincode' => $this->pincode,
            'city' => $this->city,
            'district' => $this->district,
            'state' => $this->state,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'geo_location' => $this->geo_location,
            'martial_status' => $this->martial_status,
            'education' => $this->education ? EducationResource::collection($this->education) : null,
            'languages' => $this->languages,
            'professional_details' => $this->professional_details,
            'employment_status' => $this->employment_status,
            'total_ratings' => $this->total_ratings,
            'avg_ratings' => $tutor_rating,
            'tp_points_balance' => ($this->user->points)?$this->user->points->balance:0,
            'verified_status_percentage' => $this->verified_status_percentage,
            'verified_status' => $this->verified_status,
            'fb_url' => $this->fb_url,
            'li_url' => $this->li_url,
            'tw_url' => $this->tw_url,
            'insta_url' => $this->insta_url,
            'online_cost_per_hour' => $this->online_cost_per_hour,
            'offline_cost_per_hour' => $this->offline_cost_per_hour,
            'institute_cost_per_hour' => $this->institute_cost_per_hour,
            'tutor_home_cost_per_hour' => $this->tutor_home_cost_per_hour,
            'student_home_cost_per_hour' => $this->student_home_cost_per_hour,
            'discount_limit' => $this->discount_limit,
	        'syllabus_id' => $this->preferredBoards->isNotEmpty() ? $this->preferredBoards()->first()->id : null,
            'class_id' => $this->preferredClasses->isNotEmpty() ? $this->preferredClasses()->first()->id : null,
            'subject_id' => $this->preferredSubjects->isNotEmpty() ? $this->preferredSubjects()->first()->id : null,
            'topic' => $this->topic,
            'mode_of_teaching' => $this->mode_of_classes,
            'request_received' => $this->request_received,
            'request_sent' => $this->request_sent,
            'friends_id' => $this->friends_id,
            'my_students_ids' => $this->my_students_ids,
            'my_school_id' => $this->my_school_id,
            'experience' => $this->experience ? ExperienceResource::collection($this->experience) : null,
            'tutor_commission' => $this->tutor_commission,
            'notifications' => $this->notifications,
            'parent_ids' => $this->parent_ids,
            'subject_name' => $this->preferredSubjects->isNotEmpty() ? $this->preferredSubjects()->first()->name : null,
            'class_name' => $this->preferredClasses->isNotEmpty() ? $this->preferredClasses()->first()->name : null,
            'syllabus_name' => $this->preferredSubjects->isNotEmpty() ? $this->preferredSubjects()->first()->name : null,
            'tp_id' => $this->tp_id,
            'hide_area' => $this->hide_area,
            'tutor_verified_status' => ($this->tutor_verified_status)?$this->tutor_verified_status:0,
			// 'subscribed_courses' => ($this->user->subscribeCourse)?CourseListResource::collection($this->user->subscribeCourse):null,
			'school_schedule' => ($this->user->schoolSchedule)?TutorScheduleResource::collection($this->user->schoolSchedule):null
        ];
    }
}
