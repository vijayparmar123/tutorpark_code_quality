<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentSubmittedAssignmentListResource extends JsonResource
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
            'student_status' => $this->student_status,
            'tutor_status' => $this->tutor_status,
            'obtained_mark' => $this->obtained_mark,
            'student' => ($this->student)?new UserpluckResource($this->student):null,
            'assignment' => ($this->assignment)?new AssignmentResource($this->assignment):null,
        ];
    }
}
