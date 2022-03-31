<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmittedAssignmentResource extends JsonResource
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
            'student_assignment_id' => $this->id,
            'student' => ($this->student)?new UserpluckResource($this->student):null,
            'assignment' => ($this->assignment)?new AssignmentBasicDetailResource($this->assignment):null,
            'student_status' => $this->student_status,
            'tutor_status' => $this->tutor_status,
            'obtained_mark' => $this->obtained_mark,
            'sections' => ($this->studentAssignmentSection)?SubmittedAssignmentSectionResource::collection($this->studentAssignmentSection):null,
        ];
    }
}
