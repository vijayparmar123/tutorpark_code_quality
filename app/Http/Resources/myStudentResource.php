<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class myStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = null;
        		
        if(!empty($this->availability)){
            $availability = !empty($this->availability) ? AvailabilityResource::collection($this->availability) : $this->availability; 
            $data = array_sort_by_column($availability, 'day');
        }
        // dd($this->subscribeCourse->toArray());
        return [
            'id'=>$this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role_id' => ($this->role_ids)?$this->role_ids[0]:null,
            'role_name' => $this->getRole(),
            'timezone' => $this->timezone,
            'allow_access' => $this->allow_access,
            'is_verified' => $this->is_verified,
            'linked_email' => $this->linked_email,
            // 'tutor_rating' => $tutor_rating,
            'availability'=>$this->availability ? AvailabilityResource::collection($this->availability) : null,
            'availability_group'=> $data,
            'has_school'=> $this->hasSchool(),
            'school'=> ($this->school)?new SchoolResource($this->school):null,
            'childs' => ($this->childs)?UserpluckResource::collection($this->childs):null,
            'parents' => ($this->parents)?UserpluckResource::collection($this->parents):null,
            'subscribed_course' => ($this->subscribeCourse)?SubscribedCourseListResource::collection($this->subscribeCourse()->where(['user_id'=>auth()->user()->_id])->get()):null,
            // 'subscribed_course' => ($this->details->subscribed_tuitions)?SubscribedCourseListResource::collection($this->subscribeCourse()->where(['user_id'=>auth()->user()->_id])->get()):null,
            'tuition' => ($this->details->subscribed_tuitions)?$this->details->subscribed_tuitions()->where(['tutor_id'=>auth()->user()->_id])->get():null,
            'user_details' => new UserDetailsResource($this->details),
        ];
    }
}
