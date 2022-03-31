<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TutorListResource extends JsonResource
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
		if($this->hasRole('tutor'))
		{
			$tutor_rating = getTutorAVGRating($this->_id);
		}

        return [
            'name' => $this->full_name,
            'image' => url('storage/images/user/'.strtolower($this->details->gender).".jpg"),
            'email' => $this->email,
            'gender' => $this->details->gender,
            'phone' => $this->details->phone,
            'role_id' => $this->getRole(),
            'tp_id' => $this->tp_id,
            'experience' => $this->totalExperience(),
            'degree' => $this->details->education->implode('degree',','),
            'tp_points' => ($this->points)?$this->points->balance:0,
            'total_students' => sizeof($this->details->my_students_ids),
            'timezone' => $this->timezone,
            'institute' => 'Bawnpaly Academy, Hyderabad',
            'city' => $this->details->city,
            'address' => $this->details->address,
            'syllabuses' => $this->details->preferredBoards->pluck('name'),
            'classes' => $this->details->preferredClasses->pluck('name'),
            'subjects' => $this->details->preferredSubjects->pluck('name'),
            'topic' => $this->details->topic,
            'mode_of_classes' => str_replace('_',' ',ucwords($this->details->mode_of_classes,'_')),
            'type' => null,
            'avg_ratings' => $tutor_rating,
            'tutor_verified_status' => ($this->details->tutor_verified_status)?$this->details->tutor_verified_status:0,
        ];
    }
}
